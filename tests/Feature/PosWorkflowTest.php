<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shift;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * End-to-end coverage of the money and stock flows: selling, voiding, returns,
 * shift cash-up, transfers, adjustments, stock entries and access control.
 */
class PosWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Branch $main;
    private Branch $stall;
    private User $owner;
    private User $cashier;
    private ProductVariant $sweater;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.store_mode' => 'standalone', 'app.default_branch' => 'Main Shop']);
        $this->seed([RolesSeeder::class, PermissionsSeeder::class]);

        $this->main  = Branch::create(['name' => 'Main Shop', 'code' => 'BR-01', 'is_active' => true]);
        $this->stall = Branch::create(['name' => 'STALL 35', 'code' => 'BR-02', 'is_active' => true]);

        $this->owner = User::factory()->create(['branch_id' => $this->main->id, 'branch_name' => 'Main Shop']);
        $this->owner->assignRole('owner');

        $this->cashier = User::factory()->create(['branch_id' => $this->main->id, 'branch_name' => 'Main Shop']);
        $this->cashier->assignRole('cashier');

        $category = Category::create(['name' => 'Sweaters']);
        $product  = Product::create(['name' => 'Accurate Sweater', 'sku_prefix' => 'ACC-SW', 'category_id' => $category->id]);
        $this->sweater = ProductVariant::create([
            'product_id'    => $product->id,
            'sku'           => 'ACC-SW-24',
            'barcode'       => 'BC-ACC-SW-24',
            'size'          => '24',
            'selling_price' => 500,
            'cost_price'    => 300,
            'stock_quantity'=> 0,
        ]);
        $this->sweater->setStockForBranch('Main Shop', 10);
    }

    // ── helpers ────────────────────────────────────────────────────────────

    private function stockAt(string $branch): float
    {
        return (float) BranchStock::where('branch_name', $branch)
            ->where('product_variant_id', $this->sweater->id)
            ->value('stock_quantity');
    }

    private function openShift(User $user, float $float = 1000): Shift
    {
        $this->actingAs($user)->post(route('shifts.open.store'), ['starting_float' => $float]);

        return Shift::where('user_id', $user->id)->where('status', 'open')->firstOrFail();
    }

    private function sell(User $user, float $qty, string $method = 'Cash', array $extra = []): Sale
    {
        $this->actingAs($user)->post(route('pos.store'), array_merge([
            'items'          => [['variant_id' => $this->sweater->id, 'quantity' => $qty]],
            'payment_method' => $method,
        ], $extra))->assertSessionHasNoErrors();

        return Sale::latest('id')->firstOrFail();
    }

    // ── access control ─────────────────────────────────────────────────────

    public function test_cashier_cannot_promote_themselves_via_patch(): void
    {
        $this->actingAs($this->cashier)->patch(route('users.update', $this->cashier), [
            'name'      => $this->cashier->name,
            'username'  => $this->cashier->username,
            'email'     => $this->cashier->email,
            'is_active' => 1,
            'role'      => 'owner',
        ]);

        $this->assertFalse($this->cashier->fresh()->hasRole('owner'));
    }

    public function test_cashier_cannot_edit_role_permissions(): void
    {
        $cashierRole = Role::findByName('cashier');

        $this->actingAs($this->cashier)->put(route('roles.update', $cashierRole), [
            'permissions' => ['delete-users', 'void-sales'],
        ]);

        $this->assertFalse($cashierRole->fresh()->hasPermissionTo('delete-users'));
    }

    public function test_cashier_cannot_record_purchases(): void
    {
        $this->actingAs($this->cashier)->post(route('purchases.store'), [
            'supplier_name' => 'Supplier',
            'branch_name'   => 'Main Shop',
            'purchase_date' => '2026-11-23',
            'status'        => 'received',
            'items'         => [['item_name' => 'Sweater', 'product_variant_id' => $this->sweater->id, 'unit_cost' => 1, 'quantity' => 100]],
        ]);

        $this->assertEquals(10, $this->stockAt('Main Shop'));
    }

    // ── sales & voids ──────────────────────────────────────────────────────

    public function test_sale_reduces_branch_stock_and_updates_shift(): void
    {
        $shift = $this->openShift($this->cashier);
        $sale  = $this->sell($this->cashier, 2);

        $this->assertEquals(8, $this->stockAt('Main Shop'));
        $this->assertEquals(1000, (float) $sale->total_amount);
        $this->assertEquals(2000, (float) $shift->fresh()->expected_cash); // 1000 float + 1000 cash
        $this->assertDatabaseHas('stock_movements', ['reference_id' => $sale->id, 'movement_type' => 'sale']);
    }

    public function test_split_payment_must_match_total(): void
    {
        $this->openShift($this->cashier);

        $this->actingAs($this->cashier)->post(route('pos.store'), [
            'items'          => [['variant_id' => $this->sweater->id, 'quantity' => 1]],
            'payment_method' => 'Split',
            'cash_amount'    => 100,
            'mpesa_amount'   => 100,
        ])->assertSessionHasErrors('pos_error');

        $this->assertEquals(0, Sale::count());
        $this->assertEquals(10, $this->stockAt('Main Shop'));
    }

    public function test_split_payment_cash_portion_counts_toward_drawer(): void
    {
        $shift = $this->openShift($this->cashier);
        $this->sell($this->cashier, 1, 'Split', ['cash_amount' => 200.50, 'mpesa_amount' => 299.50]);

        $shift->refresh();
        $this->assertEquals(1200.50, (float) $shift->expected_cash);
        $this->assertEquals(299.50, (float) $shift->mpesa_total);
    }

    public function test_owner_can_void_sale_and_stock_and_shift_are_restored(): void
    {
        $shift = $this->openShift($this->cashier);
        $sale  = $this->sell($this->cashier, 3);

        $this->actingAs($this->owner)->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.index'));

        $this->assertModelMissing($sale);
        $this->assertEquals(10, $this->stockAt('Main Shop'));
        $shift->refresh();
        $this->assertEquals(1000, (float) $shift->expected_cash);
        $this->assertEquals(0, $shift->total_sales_count);
        $this->assertDatabaseHas('stock_movements', ['reference_id' => $sale->id, 'movement_type' => 'sale_void']);
    }

    public function test_sale_with_returns_cannot_be_voided(): void
    {
        $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 3);

        $this->actingAs($this->owner)->post(route('returns.store'), [
            'sale_id'       => $sale->id,
            'refund_method' => 'cash',
            'items'         => [['sale_item_id' => $sale->items()->first()->id, 'quantity' => 1]],
        ]);

        $this->actingAs($this->owner)->delete(route('sales.destroy', $sale));

        $this->assertModelExists($sale);
        $this->assertEquals(8, $this->stockAt('Main Shop')); // 10 - 3 sold + 1 returned, not restocked twice
    }

    // ── returns & shift cash ───────────────────────────────────────────────

    public function test_cash_refund_reduces_expected_drawer_cash_and_detail_page_loads(): void
    {
        $drawer = $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 2);

        // The owner refunds at the counter: the cash leaves the branch's open drawer
        $this->actingAs($this->owner)->post(route('returns.store'), [
            'sale_id'       => $sale->id,
            'refund_method' => 'cash',
            'items'         => [['sale_item_id' => $sale->items()->first()->id, 'quantity' => 1]],
        ])->assertSessionHasNoErrors();

        $return = SaleReturn::firstOrFail();
        $this->assertSame($drawer->id, $return->shift_id);
        $this->assertEquals(9, $this->stockAt('Main Shop'));
        $this->assertEquals(0, Shift::where('user_id', $this->owner->id)->count()); // no phantom drawer

        // Recalculation (as done at shift close) must keep the refund deducted: 1000 + 1000 - 500
        $drawer->refresh()->recalculateTotals();
        $this->assertEquals(1500, (float) $drawer->expected_cash);

        $this->actingAs($this->owner)->get(route('returns.show', $return))
            ->assertOk()
            ->assertSee($return->return_number)
            ->assertSee($sale->receipt_number);
    }

    public function test_cannot_return_more_than_was_sold(): void
    {
        $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 1);

        $this->actingAs($this->owner)->post(route('returns.store'), [
            'sale_id'       => $sale->id,
            'refund_method' => 'cash',
            'items'         => [['sale_item_id' => $sale->items()->first()->id, 'quantity' => 2]],
        ])->assertSessionHasErrors('return_error');

        $this->assertEquals(0, SaleReturn::count());
    }

    public function test_credit_sale_return_reduces_customer_balance_and_blocks_cash_refund(): void
    {
        $customer = Customer::create(['first_name' => 'School', 'phone' => '0711000000']);
        $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 2, 'Credit', ['customer_id' => $customer->id]);
        $this->assertEquals(1000, $customer->fresh()->outstanding_balance);

        $itemId = $sale->items()->first()->id;

        $this->actingAs($this->owner)->post(route('returns.store'), [
            'sale_id' => $sale->id, 'refund_method' => 'cash',
            'items'   => [['sale_item_id' => $itemId, 'quantity' => 1]],
        ])->assertSessionHasErrors('return_error');

        $this->actingAs($this->owner)->post(route('returns.store'), [
            'sale_id' => $sale->id, 'refund_method' => 'store_credit',
            'items'   => [['sale_item_id' => $itemId, 'quantity' => 1]],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(500, $customer->fresh()->outstanding_balance);
    }

    public function test_cash_debt_repayment_is_added_to_drawer(): void
    {
        $customer = Customer::create(['first_name' => 'School', 'phone' => '0711000001']);
        $shift = $this->openShift($this->cashier);

        $this->actingAs($this->cashier)->post(route('customers.payments.store', $customer), [
            'amount' => 300, 'payment_method' => 'cash',
        ]);

        $shift->refresh()->recalculateTotals();
        $this->assertEquals(1300, (float) $shift->expected_cash);
    }

    // ── stock movements between and within branches ────────────────────────

    public function test_transfer_is_rejected_when_source_branch_lacks_stock(): void
    {
        $this->actingAs($this->owner)->post(route('transfers.store'), [
            'from_branch'   => 'Main Shop',
            'to_branch'     => 'STALL 35',
            'transfer_date' => '2026-11-23',
            'items'         => [['product_variant_id' => $this->sweater->id, 'quantity' => 50]],
        ])->assertSessionHas('error');

        $this->assertEquals(10, $this->stockAt('Main Shop'));
        $this->assertEquals(0, $this->stockAt('STALL 35'));
        $this->assertEquals(0, StockTransfer::count());
    }

    public function test_transfer_moves_stock_once_even_if_confirmed_twice(): void
    {
        $this->actingAs($this->owner)->post(route('transfers.store'), [
            'from_branch'   => 'Main Shop',
            'to_branch'     => 'STALL 35',
            'transfer_date' => '2026-11-23',
            'items'         => [['product_variant_id' => $this->sweater->id, 'quantity' => 4]],
        ]);

        $transfer = StockTransfer::firstOrFail();
        $this->assertEquals(6, $this->stockAt('Main Shop'));

        $this->actingAs($this->owner)->post(route('transfers.confirm', $transfer));
        $this->actingAs($this->owner)->post(route('transfers.confirm', $transfer));

        $this->assertEquals(4, $this->stockAt('STALL 35'));
        $this->assertEquals(2, StockMovement::where('reference_id', $transfer->id)->whereIn('movement_type', ['transfer_out', 'transfer_in'])->count());
    }

    public function test_stock_adjustment_changes_sellable_branch_stock(): void
    {
        $this->actingAs($this->owner)->post(route('adjustments.store'), [
            'branch_name' => 'Main Shop',
            'reason'      => 'Damaged Goods',
            'items'       => [['product_variant_id' => $this->sweater->id, 'type' => 'subtract', 'quantity' => 3]],
        ]);

        $this->assertEquals(7, $this->stockAt('Main Shop'));
        $this->assertDatabaseHas('stock_movements', ['movement_type' => 'adjustment_loss', 'product_variant_id' => $this->sweater->id]);
    }

    public function test_deleting_stock_entry_reverts_the_entrys_own_branch(): void
    {
        $this->actingAs($this->owner)->post(route('stock-entries.store'), [
            'branch_name' => 'STALL 35',
            'items'       => [['product_variant_id' => $this->sweater->id, 'quantity' => 5]],
        ]);

        $entry = StockEntry::firstOrFail();
        $this->assertSame('STALL 35', $entry->branch_name);
        $this->assertEquals(5, $this->stockAt('STALL 35'));

        // The owner's own branch is Main Shop; the reversal must still hit STALL 35
        $this->actingAs($this->owner)->delete(route('stock-entries.destroy', $entry));

        $this->assertEquals(0, $this->stockAt('STALL 35'));
        $this->assertEquals(10, $this->stockAt('Main Shop'));
    }

    public function test_renaming_a_branch_keeps_its_stock_sellable(): void
    {
        $this->actingAs($this->owner)->put(route('settings.branches.update', $this->main), [
            'name' => 'Main Shop CBD',
            'code' => 'BR-01',
        ]);

        $this->assertEquals(10, $this->stockAt('Main Shop CBD'));
        $this->assertSame('Main Shop CBD', $this->cashier->fresh()->branch_name);
    }

    // ── staff management ───────────────────────────────────────────────────

    public function test_staff_with_sales_history_cannot_be_deleted(): void
    {
        $this->openShift($this->cashier);
        $this->sell($this->cashier, 1);

        $this->actingAs($this->owner)->delete(route('users.destroy', $this->cashier))
            ->assertSessionHas('error');

        $this->assertModelExists($this->cashier);
        $this->assertEquals(1, Shift::where('user_id', $this->cashier->id)->count());
    }

    // ── shift controls ─────────────────────────────────────────────────────

    public function test_cashier_counts_blind_but_owner_sees_expected_cash(): void
    {
        $this->openShift($this->cashier);
        $this->sell($this->cashier, 1);

        $cashierView = $this->actingAs($this->cashier)->getJson(route('shifts.current'))->assertOk();
        $this->assertNull($cashierView->json('expected_cash'));
        $this->assertNull($cashierView->json('total_sales_amount'));
        $this->assertTrue($cashierView->json('blind_count'));

        $this->actingAs($this->cashier)->get(route('shifts.close.form'))
            ->assertOk()
            ->assertDontSee('1500.00')        // 1000 float + 500 sale must not leak into the page
            ->assertSee('Blind count');

        // Hand the drawer over, then the owner runs the till and sees the full breakdown
        $this->actingAs($this->owner)->post(route('shifts.force-close', Shift::where('user_id', $this->cashier->id)->first()), ['actual_cash' => 1500]);
        $this->openShift($this->owner, 0);
        $this->sell($this->owner, 1);
        $ownerView = $this->actingAs($this->owner)->getJson(route('shifts.current'))->assertOk();
        // 1500 counted at handover carries over as the opening float, plus the 500 sale
        $this->assertEquals(2000, $ownerView->json('expected_cash'));
    }

    public function test_cash_refund_is_refused_when_no_drawer_is_open(): void
    {
        $shift = $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 1);
        $this->actingAs($this->owner)->post(route('shifts.force-close', $shift), ['actual_cash' => 1500]);

        $this->actingAs($this->owner)->post(route('returns.store'), [
            'sale_id'       => $sale->id,
            'refund_method' => 'cash',
            'items'         => [['sale_item_id' => $sale->items()->first()->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('return_error');

        $this->assertEquals(0, SaleReturn::count());
        $this->assertEquals(1, Shift::count()); // nothing auto-opened
    }

    public function test_closing_without_an_open_shift_does_not_create_one(): void
    {
        $this->actingAs($this->cashier)->postJson(route('shifts.close'), ['actual_cash' => 100])
            ->assertStatus(422);
        $this->actingAs($this->cashier)->getJson(route('shifts.current'))->assertNotFound();

        $this->assertEquals(0, Shift::count());
    }

    public function test_a_till_cannot_be_opened_twice(): void
    {
        $first = $this->openShift($this->cashier);

        $second = User::factory()->create(['branch_id' => $this->main->id, 'branch_name' => 'Main Shop']);
        $second->assignRole('cashier');

        // Main Shop only has Till 1, which is in use
        $this->actingAs($second)->post(route('shifts.open.store'), [
            'starting_float' => 1000, 'register_id' => $first->register_id,
        ])->assertSessionHas('error');

        $this->assertEquals(0, Shift::where('user_id', $second->id)->count());
    }

    public function test_second_till_opens_alongside_the_first_with_its_own_float(): void
    {
        $till1 = $this->openShift($this->cashier, 1000);
        $till2Register = \App\Models\Register::create(['branch_id' => $this->main->id, 'name' => 'Till 2']);

        $second = User::factory()->create(['branch_id' => $this->main->id, 'branch_name' => 'Main Shop']);
        $second->assignRole('cashier');
        $till2 = $this->openShift($second, 300);

        $this->assertSame($till2Register->id, $till2->register_id);
        $this->assertNotSame($till1->register_id, $till2->register_id);

        // Each till's close rolls over into that till's next shift only
        $this->actingAs($second)->post(route('shifts.close'), ['actual_cash' => 300]);
        $this->actingAs($this->cashier)->post(route('shifts.close'), ['actual_cash' => 1000]);

        [$float1] = Shift::resolveOpeningFloatForRegister($till1->register);
        [$float2] = Shift::resolveOpeningFloatForRegister($till2Register);
        $this->assertEquals(1000, $float1);
        $this->assertEquals(300, $float2);
    }

    public function test_cash_refund_with_several_tills_open_needs_the_handlers_own_till(): void
    {
        $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 1);

        \App\Models\Register::create(['branch_id' => $this->main->id, 'name' => 'Till 2']);
        $second = User::factory()->create(['branch_id' => $this->main->id, 'branch_name' => 'Main Shop']);
        $second->assignRole('cashier');
        $this->openShift($second);

        // Owner has no till of their own and two drawers are open: which one paid out is unknown
        $this->actingAs($this->owner)->post(route('returns.store'), [
            'sale_id'       => $sale->id,
            'refund_method' => 'cash',
            'items'         => [['sale_item_id' => $sale->items()->first()->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('return_error');

        $this->assertEquals(0, SaleReturn::count());
    }

    public function test_owner_can_close_a_cashiers_forgotten_shift(): void
    {
        $shift = $this->openShift($this->cashier);
        $this->sell($this->cashier, 1);

        $this->actingAs($this->owner)->post(route('shifts.force-close', $shift), ['actual_cash' => 1450])
            ->assertRedirect(route('shifts.zReport', $shift));

        $shift->refresh();
        $this->assertSame('closed', $shift->status);
        $this->assertEquals(-50, (float) $shift->variance);
        $this->assertStringContainsString('Closed by', $shift->handover_notes);
    }

    public function test_cashier_cannot_force_close_another_shift(): void
    {
        $shift = $this->openShift($this->owner, 0);

        $this->actingAs($this->cashier)->post(route('shifts.force-close', $shift), ['actual_cash' => 0])
            ->assertForbidden();

        $this->assertSame('open', $shift->fresh()->status);
    }

    public function test_standalone_till_books_shift_to_the_tills_branch(): void
    {
        $stallCashier = User::factory()->create(['branch_id' => $this->stall->id, 'branch_name' => 'STALL 35']);
        $stallCashier->assignRole('cashier');

        // This till is configured as Main Shop; sales go to Main Shop, so the drawer must too
        $shift = $this->openShift($stallCashier);
        $this->assertSame('Main Shop', $shift->branch_name);

        $sale = $this->sell($stallCashier, 1);
        $this->assertSame($shift->branch_name, $sale->branch_name);
    }

    public function test_navigation_links_to_returns_intake_and_reports_overview(): void
    {
        $this->actingAs($this->owner)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('returns.index'), false)
            ->assertSee(route('inventory.opening-stock'), false)
            ->assertSee(route('inventory.factory-receipts'), false)
            ->assertSee(route('stock-entries.index'), false)
            ->assertSee(route('reports.index'), false);
    }

    // ── receipts & returns ─────────────────────────────────────────────────

    public function test_receipt_numbers_carry_the_branch_code(): void
    {
        $this->main->update(['code' => 'MS']);
        $this->openShift($this->cashier);

        $sale = $this->sell($this->cashier, 1);

        $this->assertMatchesRegularExpression('/^MS-RCP-\d{6}-[0-9A-F]{6}$/', $sale->receipt_number);
    }

    public function test_thermal_receipt_prints_shop_details_and_marks_reprints(): void
    {
        $this->main->update(['address' => 'Moi Avenue, Nairobi', 'phone' => '0711222333']);
        $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 2);

        // Straight after the sale the receipt page queues the print job automatically
        $this->actingAs($this->cashier)->get(route('pos.receipt'))
            ->assertOk()
            ->assertSee(route('sales.print', $sale), false);

        $this->actingAs($this->cashier)->get(route('sales.print', $sale))
            ->assertOk()
            ->assertSee($sale->receipt_number)
            ->assertSee('Moi Avenue, Nairobi')
            ->assertSee('0711222333')
            ->assertSee('window.print()', false)
            ->assertDontSee('COPY / REPRINT');

        $this->actingAs($this->cashier)->get(route('sales.print', ['sale' => $sale, 'copy' => 1]))
            ->assertOk()
            ->assertSee('COPY / REPRINT');
    }

    public function test_returns_are_only_accepted_at_the_shop_that_issued_the_receipt(): void
    {
        $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 1);
        // A receipt that STALL 35 issued, brought to the Main Shop PC
        $sale->update(['branch_name' => 'STALL 35']);

        $this->actingAs($this->owner)
            ->post(route('returns.store'), [
                'sale_id'       => $sale->id,
                'refund_method' => 'store_credit',
                'items'         => [['sale_item_id' => $sale->items()->first()->id, 'quantity' => 1]],
            ])->assertSessionHasErrors('return_error');

        $this->assertEquals(0, SaleReturn::count());
        $this->assertEquals(9, $this->stockAt('Main Shop'));
        $this->assertEquals(0, $this->stockAt('STALL 35'));
    }

    public function test_a_shop_pc_books_every_sale_to_its_own_shop_even_if_another_is_picked(): void
    {
        $this->stall->update(['is_active' => true]);
        $this->openShift($this->owner, 0);

        $sale = $this->sell($this->owner, 1, 'Cash', ['branch_name' => 'STALL 35']);

        $this->assertSame('Main Shop', $sale->branch_name);
        $this->assertEquals(9, $this->stockAt('Main Shop'));
        $this->assertEquals(0, $this->stockAt('STALL 35'));
    }

    // ── online (HQ) copy is view / manage only ─────────────────────────────

    public function test_online_copy_cannot_sell_open_tills_take_returns_or_change_stock(): void
    {
        $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 1);
        config(['app.store_mode' => 'cloud']);

        $this->actingAs($this->owner)->get(route('pos.index'))->assertRedirect(route('dashboard'));
        $this->actingAs($this->owner)->postJson(route('pos.store'), [
            'items' => [['variant_id' => $this->sweater->id, 'quantity' => 1]], 'payment_method' => 'Cash',
        ])->assertStatus(403);
        $this->actingAs($this->owner)->post(route('shifts.open.store'), ['starting_float' => 100])
            ->assertRedirect(route('dashboard'));
        $this->actingAs($this->owner)->post(route('returns.store'), [
            'sale_id' => $sale->id, 'refund_method' => 'store_credit',
            'items' => [['sale_item_id' => $sale->items()->first()->id, 'quantity' => 1]],
        ])->assertRedirect(route('dashboard'));
        $this->actingAs($this->owner)->delete(route('sales.destroy', $sale))->assertRedirect(route('dashboard'));
        $this->actingAs($this->owner)->post(route('transfers.store'), [
            'from_branch' => 'Main Shop', 'to_branch' => 'STALL 35',
            'items' => [['product_variant_id' => $this->sweater->id, 'quantity' => 1]],
        ])->assertRedirect(route('dashboard'));
        $this->actingAs($this->owner)->post(route('adjustments.store'), [])->assertRedirect(route('dashboard'));

        $this->assertEquals(1, Sale::count());
        $this->assertEquals(0, SaleReturn::count());
        $this->assertEquals(1, Shift::where('status', 'open')->count());
        $this->assertEquals(9, $this->stockAt('Main Shop'));
        $this->assertEquals(0, $this->stockAt('STALL 35'));
    }

    public function test_online_copy_still_shows_sales_shifts_reports_and_catalog(): void
    {
        $this->openShift($this->cashier);
        $sale = $this->sell($this->cashier, 1);
        config(['app.store_mode' => 'cloud']);

        foreach ([
            route('dashboard'), route('sales.index'), route('sales.show', $sale), route('shifts.index'),
            route('returns.index'), route('reports.index'), route('products.index'), route('transfers.index'),
            route('adjustments.index'), route('labels.index'),
        ] as $url) {
            $this->actingAs($this->owner)->get($url)->assertOk();
        }

        // The menu has no till or stock-changing links on the online copy
        $this->actingAs($this->owner)->get(route('dashboard'))
            ->assertDontSee(route('pos.index'), false)
            ->assertDontSee(route('adjustments.create'), false);
    }

    // ── barcodes and labels ────────────────────────────────────────────────

    public function test_scanning_an_exact_barcode_finds_the_item_even_in_lower_case(): void
    {
        $this->actingAs($this->cashier)->getJson(route('pos.search', ['q' => 'bc-acc-sw-24', 'exact' => 1]))
            ->assertOk()
            ->assertJsonPath('0.id', $this->sweater->id)
            ->assertJsonPath('0.exact_match', true);
    }

    public function test_a_near_miss_scan_is_not_treated_as_an_exact_match(): void
    {
        // "BC-ACC-SW" is part of the size 24 barcode, but no item has exactly that code
        $this->actingAs($this->cashier)->getJson(route('pos.search', ['q' => 'BC-ACC-SW', 'exact' => 1]))
            ->assertOk()
            ->assertJsonPath('0.exact_match', false);
    }

    public function test_barcode_stays_the_same_when_a_used_item_is_edited(): void
    {
        // The sweater has stock, so labels may already be on garments
        $this->actingAs($this->owner)->put(route('products.variants.update', [$this->sweater->product_id, $this->sweater]), [
            'size' => '26', 'colour' => 'Navy Blue', 'selling_price' => 650,
        ])->assertSessionHasNoErrors();

        $fresh = $this->sweater->fresh();
        $this->assertEquals('BC-ACC-SW-24', $fresh->barcode);
        $this->assertEquals('ACC-SW-24', $fresh->sku);
        $this->assertEquals('26', $fresh->size);
        $this->assertEquals(650, (float) $fresh->selling_price);
    }

    public function test_workshop_delivery_offers_one_label_per_piece_and_prints_scannable_barcodes(): void
    {
        $this->actingAs($this->owner)->post(route('inventory.factory-receipts.store'), [
            'delivery_note_no' => 'DN-77',
            'items'            => [['product_variant_id' => $this->sweater->id, 'quantity' => 3]],
        ])->assertSessionHas('label_entry');

        $entry = StockEntry::latest('id')->firstOrFail();
        $this->assertEquals(13, $this->stockAt('Main Shop'));

        $this->actingAs($this->owner)->get(route('labels.index', ['entry' => $entry->id]))
            ->assertOk()
            ->assertSee('BC-ACC-SW-24')
            ->assertSee('DN-77');

        $html = $this->actingAs($this->owner)->post(route('labels.print'), [
            'items'      => [['variant_id' => $this->sweater->id, 'quantity' => 3]],
            'label_size' => '50x25',
            'show_price' => 1,
            'show_business' => 1,
            'preview'    => 1,
        ])->assertOk()->assertSee('Accurate Sweater')->assertSee('KES 500')->getContent();

        $this->assertEquals(3, substr_count($html, 'aria-label="BC-ACC-SW-24"'));
        $this->assertStringContainsString(
            \App\Services\Code128::svg('BC-ACC-SW-24'),
            $html
        );
    }

    public function test_label_printing_refuses_an_empty_or_huge_batch(): void
    {
        $this->actingAs($this->owner)->post(route('labels.print'), [
            'items' => [['variant_id' => $this->sweater->id, 'quantity' => 0]], 'label_size' => '50x25',
        ])->assertSessionHasErrors('items');

        $this->actingAs($this->owner)->post(route('labels.print'), [
            'items' => [
                ['variant_id' => $this->sweater->id, 'quantity' => 1500],
                ['variant_id' => $this->sweater->id, 'quantity' => 1500],
            ],
            'label_size' => 'a4',
        ])->assertSessionHasErrors('items');
    }

    public function test_code128_encoding_matches_the_standard(): void
    {
        // Start B, "A" (value 33), checksum (104 + 33) % 103 = 34, stop
        $this->assertEquals([104, 33, 34, 106], \App\Services\Code128::encode('A'));
        // Each symbol is 11 modules wide, the stop 13, plus 10-module quiet zones each side
        $this->assertEquals(11 * 3 + 13 + 20, \App\Services\Code128::moduleCount('A'));
        $this->expectException(\InvalidArgumentException::class);
        \App\Services\Code128::encode("caf\u{e9}");
    }

    public function test_shift_parses_short_split_payment_format(): void
    {
        $shift = $this->openShift($this->cashier, 0);
        Sale::create([
            'user_id' => $this->cashier->id, 'shift_id' => $shift->id, 'receipt_number' => 'RCP-T1',
            'total_amount' => 1500, 'payment_method' => 'Split (C:1000 + M:500)', 'payment_status' => 'paid',
            'branch_name' => 'Main Shop',
        ]);

        $shift->recalculateTotals();

        $this->assertEquals(1000, (float) $shift->expected_cash);
        $this->assertEquals(500, (float) $shift->mpesa_total);
    }
}
