<?php

namespace App\Sync;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Register;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\SyncOutbox;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Applies one uploaded record at HQ, filed under the branch that owns the sync key.
 *
 * Every write is an upsert keyed by the record's uuid, so the same upload received twice
 * (e.g. the reply was lost and the shop retried) changes nothing the second time.
 * A record whose parent has not arrived yet throws SyncDeferred; the shop retries it later.
 */
class SyncReceiver
{
    public function __construct(private Branch $branch)
    {
    }

    public function apply(array $record): void
    {
        $type = $record['type'] ?? '';
        $uuid = $record['uuid'] ?? '';
        $op   = $record['op'] ?? 'upsert';
        $data = $record['data'] ?? [];

        if (!Str::isUuid($uuid) || !array_key_exists($type, SyncPayload::TYPE_ORDER)) {
            throw new \InvalidArgumentException('Malformed sync record.');
        }

        SyncOutbox::muted(fn () => DB::transaction(function () use ($type, $uuid, $op, $data) {
            if ($op === 'delete') {
                $this->delete($type, $uuid);
                return;
            }

            match ($type) {
                'customer'         => $this->customer($uuid, $data),
                'shift'            => $this->shift($uuid, $data),
                'sale'             => $this->sale($uuid, $data),
                'sale_return'      => $this->saleReturn($uuid, $data),
                'customer_payment' => $this->customerPayment($uuid, $data),
                'expense'          => $this->expense($uuid, $data),
                'stock_movement'   => $this->stockMovement($uuid, $data),
                'branch_stock'     => $this->branchStock($data),
            };
        }));
    }

    // ── record types ────────────────────────────────────────────────────────

    private function customer(string $uuid, array $d): void
    {
        $customer = Customer::withTrashed()->where('uuid', $this->resolveAlias('customer', $uuid))->first();

        if (!$customer) {
            // The same person may already exist at HQ (registered at another shop, or the
            // default walk-in customer every shop has): reuse that record via an alias.
            $existing = $this->matchCustomer($d);
            if ($existing) {
                $this->alias('customer', $uuid, $existing->uuid);
                return;
            }
            $customer = new Customer();
            $customer->uuid = $uuid;
        }

        $customer->forceFill([
            'first_name'   => $d['first_name'] ?? null,
            'last_name'    => $d['last_name'] ?? null,
            'phone'        => $this->freeUnique(Customer::class, 'phone', $d['phone'] ?? null, $customer->id),
            'email'        => $this->freeUnique(Customer::class, 'email', $d['email'] ?? null, $customer->id),
            'credit_limit' => $d['credit_limit'] ?? 0,
            'deleted_at'   => $this->time($d['deleted_at'] ?? null),
        ])->save();
    }

    private function shift(string $uuid, array $d): void
    {
        $shift = Shift::where('uuid', $uuid)->first() ?? tap(new Shift(), fn ($s) => $s->uuid = $uuid);
        $register = Register::firstOrCreate(['branch_id' => $this->branch->id, 'name' => $d['register'] ?? 'Till 1']);

        $shift->forceFill([
            'user_id'                 => $this->resolveUser($d['user'] ?? null)->id,
            'branch_id'               => $this->branch->id,
            'register_id'             => $register->id,
            'branch_name'             => $this->branch->name,
            'started_at'              => $this->time($d['started_at'] ?? null) ?? now(),
            'ended_at'                => $this->time($d['ended_at'] ?? null),
            'closed_at'               => $this->time($d['closed_at'] ?? null),
            'status'                  => $d['status'] ?? 'open',
            'opening_float'           => $d['opening_float'] ?? 0,
            'expected_cash'           => $d['expected_cash'] ?? 0,
            'actual_cash'             => $d['actual_cash'] ?? null,
            'variance'                => $d['variance'] ?? null,
            'mpesa_total'             => $d['mpesa_total'] ?? 0,
            'card_total'              => $d['card_total'] ?? 0,
            'credit_total'            => $d['credit_total'] ?? 0,
            'total_sales_count'       => $d['total_sales_count'] ?? 0,
            'total_sales_amount'      => $d['total_sales_amount'] ?? 0,
            'cash_collected_by_owner' => (bool) ($d['cash_collected_by_owner'] ?? false),
            'amount_collected'        => $d['amount_collected'] ?? null,
            'float_left_in_drawer'    => $d['float_left_in_drawer'] ?? null,
            'collected_by_name'       => $d['collected_by_name'] ?? null,
            'override_reason'         => $d['override_reason'] ?? null,
            'handover_notes'          => $d['handover_notes'] ?? null,
        ])->save();
    }

    private function sale(string $uuid, array $d): void
    {
        $sale = Sale::where('uuid', $uuid)->first() ?? tap(new Sale(), fn ($s) => $s->uuid = $uuid);

        $sale->forceFill([
            'receipt_number'    => $this->freeUnique(Sale::class, 'receipt_number', $d['receipt_number'] ?? null, $sale->id, true),
            'total_amount'      => $d['total_amount'] ?? 0,
            'payment_method'    => $d['payment_method'] ?? 'Cash',
            'payment_status'    => $d['payment_status'] ?? 'paid',
            'payment_reference' => $d['payment_reference'] ?? null,
            'branch_name'       => $this->branch->name,
            'user_id'           => $this->resolveUser($d['user'] ?? null)->id,
            'customer_id'       => $this->requireRecord(Customer::class, 'customer', $d['customer_uuid'] ?? null),
            'shift_id'          => $this->requireRecord(Shift::class, 'shift', $d['shift_uuid'] ?? null),
            'created_at'        => $this->time($d['created_at'] ?? null) ?? now(),
        ])->save();

        $keep = [];
        foreach ($d['items'] ?? [] as $i) {
            $item = SaleItem::where('uuid', $i['uuid'])->first() ?? tap(new SaleItem(), fn ($x) => $x->uuid = $i['uuid']);
            $item->forceFill([
                'sale_id'            => $sale->id,
                'product_variant_id' => $this->resolveVariant($i['variant'] ?? null)->id,
                'quantity'           => $i['quantity'] ?? 0,
                'unit_price'         => $i['unit_price'] ?? 0,
                'cost_price'         => $i['cost_price'] ?? 0,
                'line_total'         => $i['line_total'] ?? 0,
                'created_at'         => $sale->created_at,
            ])->save();
            $keep[] = $item->id;
        }
        SaleItem::where('sale_id', $sale->id)->whereNotIn('id', $keep)->delete();
    }

    private function saleReturn(string $uuid, array $d): void
    {
        $saleId = $this->requireRecord(Sale::class, 'sale', $d['sale_uuid'] ?? null);
        if (!$saleId) {
            throw new SyncDeferred('Return received before its sale.');
        }

        $return = SaleReturn::where('uuid', $uuid)->first() ?? tap(new SaleReturn(), fn ($r) => $r->uuid = $uuid);
        $return->forceFill([
            'sale_id'       => $saleId,
            'shift_id'      => $this->requireRecord(Shift::class, 'shift', $d['shift_uuid'] ?? null),
            'user_id'       => $this->resolveUser($d['user'] ?? null)->id,
            'branch_name'   => $this->branch->name,
            'return_number' => $this->freeUnique(SaleReturn::class, 'return_number', $d['return_number'] ?? null, $return->id, true),
            'refund_total'  => $d['refund_total'] ?? 0,
            'refund_method' => $d['refund_method'] ?? 'cash',
            'reason'        => $d['reason'] ?? null,
            'created_at'    => $this->time($d['created_at'] ?? null) ?? now(),
        ])->save();

        $keep = [];
        foreach ($d['items'] ?? [] as $i) {
            $item = SaleReturnItem::where('uuid', $i['uuid'])->first() ?? tap(new SaleReturnItem(), fn ($x) => $x->uuid = $i['uuid']);
            $item->forceFill([
                'sale_return_id'     => $return->id,
                'product_variant_id' => $this->resolveVariant($i['variant'] ?? null)->id,
                'quantity'           => $i['quantity'] ?? 0,
                'unit_refund_price'  => $i['unit_refund_price'] ?? 0,
                'subtotal'           => $i['subtotal'] ?? 0,
            ])->save();
            $keep[] = $item->id;
        }
        SaleReturnItem::where('sale_return_id', $return->id)->whereNotIn('id', $keep)->delete();
    }

    private function customerPayment(string $uuid, array $d): void
    {
        $customerId = $this->requireRecord(Customer::class, 'customer', $d['customer_uuid'] ?? null);
        if (!$customerId) {
            throw new SyncDeferred('Payment received before its customer.');
        }

        $payment = CustomerPayment::where('uuid', $uuid)->first() ?? tap(new CustomerPayment(), fn ($p) => $p->uuid = $uuid);
        $payment->forceFill([
            'customer_id'    => $customerId,
            'shift_id'       => $this->requireRecord(Shift::class, 'shift', $d['shift_uuid'] ?? null),
            'user_id'        => $this->resolveUser($d['user'] ?? null)->id,
            'branch_name'    => $this->branch->name,
            'amount'         => $d['amount'] ?? 0,
            'payment_method' => $d['payment_method'] ?? 'cash',
            'reference_no'   => $d['reference_no'] ?? null,
            'notes'          => $d['notes'] ?? null,
            'created_at'     => $this->time($d['created_at'] ?? null) ?? now(),
        ])->save();
    }

    private function expense(string $uuid, array $d): void
    {
        $expense = Expense::where('uuid', $uuid)->first() ?? tap(new Expense(), fn ($e) => $e->uuid = $uuid);
        $expense->forceFill([
            'expense_number' => $this->freeUnique(Expense::class, 'expense_number', $d['expense_number'] ?? null, $expense->id, true),
            'branch_name'    => $this->branch->name,
            'category'       => $d['category'] ?? 'Other',
            'amount'         => $d['amount'] ?? 0,
            'expense_date'   => $d['expense_date'] ?? now()->toDateString(),
            'note'           => $d['note'] ?? null,
            'user_id'        => $this->resolveUser($d['user'] ?? null)->id,
            'created_at'     => $this->time($d['created_at'] ?? null) ?? now(),
        ])->save();
    }

    private function stockMovement(string $uuid, array $d): void
    {
        $movement = StockMovement::where('uuid', $uuid)->first() ?? tap(new StockMovement(), fn ($m) => $m->uuid = $uuid);
        $movement->forceFill([
            'branch_id'          => $this->branch->id,
            'branch_name'        => $this->branch->name,
            'product_variant_id' => $this->resolveVariant($d['variant'] ?? null)->id,
            'quantity'           => $d['quantity'] ?? 0,
            'balance_after'      => $d['balance_after'] ?? 0,
            'movement_type'      => $d['movement_type'] ?? 'adjustment',
            'reference_no'       => $d['reference_no'] ?? null,
            'notes'              => $d['notes'] ?? null,
            'user_id'            => isset($d['user']) ? $this->resolveUser($d['user'])->id : null,
            'created_at'         => $this->time($d['created_at'] ?? null) ?? now(),
        ])->save();
    }

    /** The shop's current shelf count for one item (a snapshot, not a movement). */
    private function branchStock(array $d): void
    {
        $variant = $this->resolveVariant($d['variant'] ?? null);

        BranchStock::updateOrCreate(
            ['branch_id' => $this->branch->id, 'product_variant_id' => $variant->id],
            ['branch_name' => $this->branch->name, 'stock_quantity' => max(0, (float) ($d['stock_quantity'] ?? 0))]
        );

        $variant->update(['stock_quantity' => $variant->branchStocks()->sum('stock_quantity')]);
    }

    private function delete(string $type, string $uuid): void
    {
        $model = SyncPayload::MODELS[$type];
        $record = $model::where('uuid', $uuid)->first();

        if ($record instanceof Sale) {
            $record->items()->delete();
        }
        $record?->delete();
    }

    // ── reference resolution ────────────────────────────────────────────────

    /**
     * Staff are matched by username. A username already used by someone at a different
     * branch gets a branch suffix, so two shops' "cashier1" stay separate people.
     */
    private function resolveUser(?array $u): User
    {
        $username = Str::slug($u['username'] ?? 'unknown-staff', '_') ?: 'unknown_staff';

        $existing = User::where('username', $username)->first();
        if ($existing && ($existing->branch_id === null || $existing->branch_id === $this->branch->id)) {
            return $existing;
        }

        // Username free at HQ: take it, as this branch's staff member
        $scoped = $existing
            ? $username . '_' . Str::lower(preg_replace('/[^A-Za-z0-9]/', '', $this->branch->code ?: (string) $this->branch->id))
            : $username;

        return User::firstOrCreate(
            ['username' => $scoped],
            [
                'name'        => $u['name'] ?? $username,
                'email'       => $scoped . '@sync.local',
                'password'    => Hash::make(Str::random(40)),
                'is_active'   => false, // a synced identity, not a login; activate at HQ if needed
                'branch_id'   => $this->branch->id,
                'branch_name' => $this->branch->name,
            ]
        );
    }

    /**
     * Products are matched by SKU (generated the same way at every shop). An item HQ has
     * never seen is created so the shop's sales still show what was sold.
     */
    private function resolveVariant(?array $v): ProductVariant
    {
        $sku = trim((string) ($v['sku'] ?? ''));
        if ($sku === '') {
            throw new \InvalidArgumentException('Sync record refers to a product without a SKU.');
        }

        $variant = ProductVariant::withTrashed()->where('sku', $sku)->first();
        if ($variant) {
            return $variant;
        }

        $category = Category::firstOrCreate(['name' => $v['category'] ?: 'Synced From Shops']);
        $productName = $v['product'] ?: $sku;

        $product = Product::withTrashed()->where('name', $productName)->first();
        if (!$product) {
            $prefix = $base = strtoupper($v['sku_prefix'] ?: Str::before($sku, '-'));
            $n = 2;
            while (Product::withTrashed()->where('sku_prefix', $prefix)->exists()) {
                $prefix = $base . '-' . $n++;
            }
            $product = Product::create(['name' => $productName, 'sku_prefix' => $prefix, 'category_id' => $category->id]);
        }

        return ProductVariant::create([
            'product_id'      => $product->id,
            'sku'             => $sku,
            'barcode'         => $this->freeUnique(ProductVariant::class, 'barcode', $v['barcode'] ?? null, null),
            'size'            => $v['size'] ?? null,
            'colour'          => $v['colour'] ?? null,
            'unit_of_measure' => $v['unit_of_measure'] ?? null,
            'selling_price'   => $v['selling_price'] ?? 0,
            'cost_price'      => $v['cost_price'] ?? 0,
            'stock_quantity'  => 0,
        ]);
    }

    /**
     * Local id of a synced record by uuid (following aliases). Null when no uuid was given.
     * Throws SyncDeferred when the uuid is given but the record has not arrived yet.
     */
    private function requireRecord(string $model, string $type, ?string $uuid): ?int
    {
        if (!$uuid) {
            return null;
        }

        $query = $model::query();
        if (method_exists($model, 'bootSoftDeletes')) {
            $query->withTrashed();
        }

        $id = $query->where('uuid', $this->resolveAlias($type, $uuid))->value('id');
        if (!$id) {
            throw new SyncDeferred("Waiting for {$type} {$uuid}.");
        }

        return $id;
    }

    private function matchCustomer(array $d): ?Customer
    {
        foreach (['phone', 'email'] as $field) {
            if (!empty($d[$field]) && ($c = Customer::withTrashed()->where($field, $d[$field])->first())) {
                return $c;
            }
        }

        return null;
    }

    private function alias(string $type, string $aliasUuid, string $targetUuid): void
    {
        DB::table('sync_aliases')->updateOrInsert(
            ['record_type' => $type, 'alias_uuid' => $aliasUuid],
            ['target_uuid' => $targetUuid, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    private function resolveAlias(string $type, string $uuid): string
    {
        return DB::table('sync_aliases')->where('record_type', $type)->where('alias_uuid', $uuid)->value('target_uuid') ?? $uuid;
    }

    /**
     * Keep a unique column unique at HQ. Document numbers that clash (older numbers had no
     * branch code) get the branch code appended; other clashing values are dropped.
     */
    private function freeUnique(string $model, string $column, ?string $value, ?int $ownId, bool $suffixOnClash = false): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $query = $model::query();
        if (method_exists($model, 'bootSoftDeletes')) {
            $query->withTrashed();
        }
        $clash = $query->where($column, $value)->when($ownId, fn ($q) => $q->where('id', '!=', $ownId))->exists();

        if (!$clash) {
            return $value;
        }

        return $suffixOnClash ? $value . '-' . strtoupper($this->branch->code ?: $this->branch->id) : null;
    }

    private function time(?string $value): ?Carbon
    {
        return $value ? Carbon::parse($value)->setTimezone(config('app.timezone')) : null;
    }
}
