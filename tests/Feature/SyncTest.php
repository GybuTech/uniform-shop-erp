<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\SyncOutbox;
use App\Models\User;
use App\Sync\SyncPayload;
use App\Sync\SyncPusher;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Shop → HQ sync: outbox recording, upload, idempotency, ordering and access control.
 *
 * One test database plays both roles: data is produced in shop (standalone) mode,
 * captured as upload documents, the shop's rows are removed, and the documents are
 * then received in HQ (cloud) mode, exactly as a separate HQ server would receive them.
 */
class SyncTest extends TestCase
{
    use RefreshDatabase;

    private Branch $main;
    private Branch $hqBranch;
    private User $cashier;
    private User $owner;
    private ProductVariant $sweater;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.store_mode' => 'standalone', 'app.default_branch' => 'Main Shop']);
        $this->seed([RolesSeeder::class, PermissionsSeeder::class]);

        $this->main = Branch::create(['name' => 'Main Shop', 'code' => 'MS', 'is_active' => true]);
        // At HQ this shop is registered under a different name, to prove HQ files data by sync key
        $this->hqBranch = Branch::create(['name' => 'Main Shop (Moi Avenue)', 'code' => 'MSA', 'is_active' => true]);

        $this->owner = User::factory()->create(['username' => 'owner', 'branch_id' => $this->main->id, 'branch_name' => 'Main Shop']);
        $this->owner->assignRole('owner');
        $this->cashier = User::factory()->create(['username' => 'jane', 'name' => 'Jane Cashier', 'branch_id' => $this->main->id, 'branch_name' => 'Main Shop']);
        $this->cashier->assignRole('cashier');

        $category = Category::create(['name' => 'Sweaters']);
        $product = Product::create(['name' => 'Imara Sweater', 'sku_prefix' => 'IMS', 'category_id' => $category->id]);
        $this->sweater = ProductVariant::create([
            'product_id' => $product->id, 'sku' => 'IMS-24', 'barcode' => 'BC-IMS-24', 'size' => '24',
            'selling_price' => 1200, 'cost_price' => 700, 'stock_quantity' => 0,
        ]);
        $this->sweater->setStockForBranch('Main Shop', 10);
    }

    // ── helpers ────────────────────────────────────────────────────────────

    /** Make shop activity: a cash sale, a credit sale with a partial return, and a repayment. */
    private function shopActivity(): array
    {
        $customer = Customer::create(['first_name' => 'Imara', 'last_name' => 'Primary', 'phone' => '0722000111', 'credit_limit' => 0]);

        $this->actingAs($this->cashier)->post(route('shifts.open.store'), ['starting_float' => 1000]);
        $this->actingAs($this->cashier)->post(route('pos.store'), [
            'items' => [['variant_id' => $this->sweater->id, 'quantity' => 2]], 'payment_method' => 'Cash',
        ])->assertSessionHasNoErrors();
        $this->actingAs($this->cashier)->post(route('pos.store'), [
            'items' => [['variant_id' => $this->sweater->id, 'quantity' => 3]], 'payment_method' => 'Credit', 'customer_id' => $customer->id,
        ])->assertSessionHasNoErrors();

        $creditSale = Sale::where('payment_status', 'credit')->firstOrFail();
        $this->actingAs($this->owner)->post(route('returns.store'), [
            'sale_id' => $creditSale->id, 'refund_method' => 'store_credit',
            'items' => [['sale_item_id' => $creditSale->items()->first()->id, 'quantity' => 1]],
        ])->assertSessionHasNoErrors();
        $this->actingAs($this->cashier)->post(route('customers.payments.store', $customer), ['amount' => 1000, 'payment_method' => 'cash']);

        return [$customer, $creditSale];
    }

    /** Everything the shop would upload right now, in upload order. */
    private function captureUpload(): array
    {
        return SyncOutbox::whereNull('sent_at')
            ->selectRaw('record_type, record_uuid, MAX(id) as max_id')
            ->groupBy('record_type', 'record_uuid')
            ->get()
            ->sortBy(fn ($r) => [SyncPayload::TYPE_ORDER[$r->record_type], $r->max_id])
            ->map(fn ($r) => SyncPayload::build($r->record_type, $r->record_uuid))
            ->filter()
            ->values()
            ->all();
    }

    /** Remove the shop's transactional rows so HQ starts from a clean slate. */
    private function wipeShopRows(): void
    {
        SyncOutbox::muted(function () {
            SaleReturnItem::query()->delete();
            SaleReturn::query()->delete();
            SaleItem::query()->delete();
            Sale::query()->delete();
            CustomerPayment::query()->delete();
            StockMovement::query()->delete();
            Shift::query()->delete();
            Customer::withTrashed()->forceDelete();
            BranchStock::query()->delete();
        });
    }

    private function becomeHq(): string
    {
        config(['app.store_mode' => 'cloud']);
        $key = 'shop_test_key_for_main_shop';
        $this->hqBranch->forceFill(['sync_token_hash' => hash('sha256', $key)])->save();

        return $key;
    }

    private function push(string $key, array $records, int $pending = 0)
    {
        return $this->withToken($key)->postJson('/api/sync/push', ['records' => $records, 'pending_total' => $pending]);
    }

    // ── tests ──────────────────────────────────────────────────────────────

    public function test_shop_changes_are_recorded_in_the_outbox(): void
    {
        $this->shopActivity();

        $types = SyncOutbox::distinct()->pluck('record_type')->all();
        foreach (['customer', 'shift', 'sale', 'sale_return', 'customer_payment', 'stock_movement', 'branch_stock'] as $type) {
            $this->assertContains($type, $types, "No outbox entry for {$type}");
        }

        // Sale lines travel inside their sale: no separate record type for them
        $this->assertNotContains('sale_item', $types);
        $this->assertTrue(Sale::all()->every(fn ($s) => SyncOutbox::where('record_uuid', $s->uuid)->exists()));
    }

    public function test_hq_receives_a_shops_activity_filed_under_that_shop(): void
    {
        [$customer, $creditSale] = $this->shopActivity();
        $upload = $this->captureUpload();
        $saleUuids = Sale::pluck('uuid')->all();
        $receipt = $creditSale->receipt_number;

        $this->wipeShopRows();
        $key = $this->becomeHq();

        $response = $this->push($key, $upload)->assertOk();
        $this->assertSame([], $response->json('failed'));
        $this->assertSame([], $response->json('deferred'));
        $this->assertCount(count($upload), $response->json('accepted'));

        // Sales arrive with their lines, same uuids and receipt numbers, under HQ's branch name
        $this->assertEqualsCanonicalizing($saleUuids, Sale::pluck('uuid')->all());
        $sale = Sale::where('receipt_number', $receipt)->firstOrFail();
        $this->assertSame('Main Shop (Moi Avenue)', $sale->branch_name);
        $this->assertEquals(3600, (float) $sale->total_amount);
        $this->assertSame('IMS-24', $sale->items()->first()->productVariant->sku);

        // Staff travel by username. "jane" already belongs to another branch's staff here, so this
        // shop's Jane is kept as a separate person, suffixed with the branch code.
        $shiftUser = Shift::firstOrFail()->user;
        $this->assertSame('jane_msa', $shiftUser->username);
        $this->assertSame('Jane Cashier', $shiftUser->name);
        $this->assertFalse((bool) $shiftUser->is_active); // a record of who sold, not a login

        // Shift, return, repayment and stock all arrive
        $this->assertEquals(1, SaleReturn::count());
        $this->assertEquals(1, CustomerPayment::count());
        $this->assertEquals(6, (float) BranchStock::where('branch_id', $this->hqBranch->id)->value('stock_quantity')); // 10 - 2 - 3 + 1
        $this->assertGreaterThan(0, StockMovement::where('branch_name', 'Main Shop (Moi Avenue)')->count());

        // Credit balance at HQ: 3600 credit - 1200 returned - 1000 repaid
        $this->assertEquals(1400, Customer::where('uuid', $customer->uuid)->firstOrFail()->outstanding_balance);

        // HQ knows when this shop last synced
        $this->assertNotNull($this->hqBranch->fresh()->last_synced_at);
    }

    public function test_owner_sees_a_shops_sales_shifts_and_low_stock_online_after_it_syncs(): void
    {
        [, $creditSale] = $this->shopActivity();
        // One more sale takes the sweater down to 4 pieces: low stock (5 or fewer)
        $this->actingAs($this->cashier)->post(route('pos.store'), [
            'items' => [['variant_id' => $this->sweater->id, 'quantity' => 2]], 'payment_method' => 'Cash',
        ])->assertSessionHasNoErrors();
        $receipts = Sale::pluck('receipt_number')->all();
        $upload = $this->captureUpload();

        $this->wipeShopRows();
        $key = $this->becomeHq();
        $this->push($key, $upload)->assertOk();

        $shop = 'Main Shop (Moi Avenue)';
        $this->assertEquals(4, (float) BranchStock::where('branch_name', $shop)->value('stock_quantity'));
        $this->assertEquals(4, (float) $this->sweater->fresh()->stock_quantity); // company-wide total too

        // From anywhere, the owner opens the online copy: that shop's dashboard shows the low stock
        $this->actingAs($this->owner)->get(route('dashboard', ['branch' => $shop]))
            ->assertOk()
            ->assertSee('Imara Sweater');
        // ...and so does the all-shops view
        $this->actingAs($this->owner)->get(route('dashboard', ['branch' => 'all']))
            ->assertOk()
            ->assertSee('Imara Sweater');

        // Every receipt the shop issued, the cash-up by Jane, the return, and the reports
        $salesPage = $this->actingAs($this->owner)->get(route('sales.index'))->assertOk();
        foreach ($receipts as $receipt) {
            $salesPage->assertSee($receipt);
        }
        $this->actingAs($this->owner)->get(route('sales.show', Sale::where('receipt_number', $creditSale->receipt_number)->first()))
            ->assertOk()->assertSee('Imara Sweater');
        $this->actingAs($this->owner)->get(route('shifts.index'))->assertOk()->assertSee('Jane Cashier');
        $this->actingAs($this->owner)->get(route('returns.index'))->assertOk();
        $this->actingAs($this->owner)->get(route('reports.index'))->assertOk();
        $this->actingAs($this->owner)->get(route('settings.index'))->assertOk()->assertSee($shop);
    }

    public function test_receiving_the_same_upload_twice_creates_no_duplicates(): void
    {
        $this->shopActivity();
        $upload = $this->captureUpload();
        $this->wipeShopRows();
        $key = $this->becomeHq();

        $this->push($key, $upload)->assertOk();
        $counts = [Sale::count(), SaleItem::count(), SaleReturn::count(), Shift::count(), StockMovement::count(), CustomerPayment::count()];

        $this->push($key, $upload)->assertOk();
        $this->assertSame($counts, [Sale::count(), SaleItem::count(), SaleReturn::count(), Shift::count(), StockMovement::count(), CustomerPayment::count()]);
    }

    public function test_a_record_arriving_before_its_parent_waits_instead_of_failing(): void
    {
        $this->shopActivity();
        $upload = collect($this->captureUpload());
        $this->wipeShopRows();
        $key = $this->becomeHq();

        $returnOnly = $upload->where('type', 'sale_return')->values()->all();
        $response = $this->push($key, $returnOnly)->assertOk();
        $this->assertCount(1, $response->json('deferred'));
        $this->assertSame([], $response->json('failed'));

        // Once the parents arrive, the return goes through
        $this->push($key, $upload->all())->assertOk();
        $this->assertEquals(1, SaleReturn::count());
    }

    public function test_walk_in_customer_registered_at_every_shop_is_not_duplicated_at_hq(): void
    {
        $key = $this->becomeHq();
        $hqWalkIn = Customer::create(['first_name' => 'Walk-in', 'last_name' => 'Customer', 'phone' => '0700000000']);

        $shopWalkIn = ['type' => 'customer', 'uuid' => (string) \Illuminate\Support\Str::uuid(), 'op' => 'upsert',
            'data' => ['first_name' => 'Walk-in', 'last_name' => 'Customer', 'phone' => '0700000000', 'credit_limit' => 0]];

        $this->push($key, [$shopWalkIn])->assertOk()->assertJsonPath('accepted.0', $shopWalkIn['uuid']);

        $this->assertEquals(1, Customer::where('phone', '0700000000')->count());
        $this->assertDatabaseHas('sync_aliases', ['alias_uuid' => $shopWalkIn['uuid'], 'target_uuid' => $hqWalkIn->uuid]);
    }

    public function test_hq_rejects_unknown_keys_and_shops_do_not_accept_uploads(): void
    {
        $this->becomeHq();
        $this->withToken('not-a-real-key')->postJson('/api/sync/push', ['records' => []])->assertUnauthorized();

        config(['app.store_mode' => 'standalone']);
        $this->withToken('shop_test_key_for_main_shop')->postJson('/api/sync/push', ['records' => []])->assertNotFound();
    }

    public function test_pusher_uploads_history_once_and_marks_confirmed_entries_sent(): void
    {
        $this->shopActivity();
        config(['sync.hq_url' => 'https://hq.example.test', 'sync.token' => 'shop_key']);

        Http::fake(['hq.example.test/*' => function ($request) {
            return Http::response(['accepted' => array_column($request->data()['records'], 'uuid'), 'deferred' => [], 'failed' => []]);
        }]);

        $result = app(SyncPusher::class)->run();

        $this->assertSame('ok', $result['status']);
        $this->assertGreaterThan(0, $result['sent']);
        $this->assertSame(0, SyncOutbox::pendingCount());
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer shop_key') && str_ends_with($r->url(), '/api/sync/push'));

        // A second run only sends new changes, not the history again
        Http::fake(['hq.example.test/*' => Http::response(['accepted' => [], 'deferred' => [], 'failed' => []])]);
        $this->assertSame(0, app(SyncPusher::class)->run()['sent']);
    }

    public function test_offline_shop_keeps_everything_queued(): void
    {
        $this->shopActivity();
        config(['sync.hq_url' => 'https://hq.example.test', 'sync.token' => 'shop_key']);
        $before = SyncOutbox::pendingCount();

        Http::fake(fn () => throw new ConnectionException('Could not resolve host'));

        $result = app(SyncPusher::class)->run();

        $this->assertSame('offline', $result['status']);
        $this->assertGreaterThanOrEqual($before, SyncOutbox::pendingCount());
        $this->assertSame(0, SyncOutbox::whereNotNull('sent_at')->count());
    }

    public function test_nothing_is_sent_until_sync_is_configured(): void
    {
        Http::fake();
        $this->shopActivity();

        $this->assertSame('not_configured', app(SyncPusher::class)->run()['status']);
        Http::assertNothingSent();
        $this->assertGreaterThan(0, SyncOutbox::pendingCount()); // kept, ready for go-live
    }
}
