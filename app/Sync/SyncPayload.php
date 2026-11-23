<?php

namespace App\Sync;

use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\User;

/**
 * Builds the upload document for one record (shop side).
 *
 * Local numeric ids mean nothing at HQ, so references travel as:
 *   - other synced records  -> their uuid
 *   - products              -> SKU plus enough detail for HQ to create it if missing
 *   - staff                 -> username and name
 * Branch names are NOT trusted from the payload: HQ files everything under the branch
 * that owns the sync key.
 */
class SyncPayload
{
    /** Upload order: parents before the records that refer to them. */
    public const TYPE_ORDER = [
        'customer'         => 1,
        'shift'            => 2,
        'sale'             => 3,
        'sale_return'      => 4,
        'customer_payment' => 5,
        'expense'          => 6,
        'stock_movement'   => 7,
        'branch_stock'     => 8,
    ];

    /** Every synced model, used for the one-off upload of existing history. */
    public const MODELS = [
        'customer'         => Customer::class,
        'shift'            => Shift::class,
        'sale'             => Sale::class,
        'sale_return'      => SaleReturn::class,
        'customer_payment' => CustomerPayment::class,
        'expense'          => Expense::class,
        'stock_movement'   => StockMovement::class,
        'branch_stock'     => BranchStock::class,
    ];

    /**
     * @return array|null null when the record no longer exists (it was deleted after queueing)
     */
    public static function build(string $type, string $uuid): ?array
    {
        $data = match ($type) {
            'customer'         => static::customer($uuid),
            'shift'            => static::shift($uuid),
            'sale'             => static::sale($uuid),
            'sale_return'      => static::saleReturn($uuid),
            'customer_payment' => static::customerPayment($uuid),
            'expense'          => static::expense($uuid),
            'stock_movement'   => static::stockMovement($uuid),
            'branch_stock'     => static::branchStock($uuid),
            default            => throw new \InvalidArgumentException("Unknown sync type '{$type}'."),
        };

        return $data === null ? null : ['type' => $type, 'uuid' => $uuid, 'op' => 'upsert', 'data' => $data];
    }

    private static function customer(string $uuid): ?array
    {
        $c = Customer::withTrashed()->where('uuid', $uuid)->first();

        return $c ? [
            'first_name'   => $c->first_name,
            'last_name'    => $c->last_name,
            'phone'        => $c->phone,
            'email'        => $c->email,
            'credit_limit' => (float) $c->credit_limit,
            'deleted_at'   => $c->deleted_at?->toIso8601String(),
            'created_at'   => $c->created_at?->toIso8601String(),
        ] : null;
    }

    private static function shift(string $uuid): ?array
    {
        $s = Shift::with(['user', 'register'])->where('uuid', $uuid)->first();
        if (!$s) {
            return null;
        }

        return [
            'user'          => static::user($s->user),
            'register'      => $s->register?->name ?? 'Till 1',
            'started_at'    => $s->started_at?->toIso8601String(),
            'ended_at'      => $s->ended_at?->toIso8601String(),
            'closed_at'     => $s->closed_at?->toIso8601String(),
            'status'        => $s->status,
            'opening_float' => (float) $s->opening_float,
            'expected_cash' => (float) $s->expected_cash,
            'actual_cash'   => $s->actual_cash === null ? null : (float) $s->actual_cash,
            'variance'      => $s->variance === null ? null : (float) $s->variance,
            'mpesa_total'   => (float) $s->mpesa_total,
            'card_total'    => (float) $s->card_total,
            'credit_total'  => (float) $s->credit_total,
            'total_sales_count'       => (int) $s->total_sales_count,
            'total_sales_amount'      => (float) $s->total_sales_amount,
            'cash_collected_by_owner' => (bool) $s->cash_collected_by_owner,
            'amount_collected'        => $s->amount_collected === null ? null : (float) $s->amount_collected,
            'float_left_in_drawer'    => $s->float_left_in_drawer === null ? null : (float) $s->float_left_in_drawer,
            'collected_by_name'       => $s->collected_by_name,
            'override_reason'         => $s->override_reason,
            'handover_notes'          => $s->handover_notes,
        ];
    }

    private static function sale(string $uuid): ?array
    {
        $s = Sale::with(['user', 'customer', 'shift', 'saleItems.productVariant.product.category'])->where('uuid', $uuid)->first();
        if (!$s) {
            return null;
        }

        return [
            'receipt_number'    => $s->receipt_number,
            'total_amount'      => (float) $s->total_amount,
            'payment_method'    => $s->payment_method,
            'payment_status'    => $s->payment_status,
            'payment_reference' => $s->payment_reference,
            'user'              => static::user($s->user),
            'customer_uuid'     => $s->customer?->uuid,
            'shift_uuid'        => $s->shift?->uuid,
            'created_at'        => $s->created_at?->toIso8601String(),
            'items'             => $s->saleItems->map(fn ($i) => [
                'uuid'       => $i->uuid,
                'variant'    => static::variant($i->productVariant),
                'quantity'   => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
                'cost_price' => (float) $i->cost_price,
                'line_total' => (float) $i->line_total,
            ])->values()->all(),
        ];
    }

    private static function saleReturn(string $uuid): ?array
    {
        $r = SaleReturn::with(['user', 'sale', 'shift', 'items.productVariant.product.category'])->where('uuid', $uuid)->first();
        if (!$r) {
            return null;
        }

        return [
            'sale_uuid'     => $r->sale?->uuid,
            'shift_uuid'    => $r->shift?->uuid,
            'user'          => static::user($r->user),
            'return_number' => $r->return_number,
            'refund_total'  => (float) $r->refund_total,
            'refund_method' => $r->refund_method,
            'reason'        => $r->reason,
            'created_at'    => $r->created_at?->toIso8601String(),
            'items'         => $r->items->map(fn ($i) => [
                'uuid'              => $i->uuid,
                'variant'           => static::variant($i->productVariant),
                'quantity'          => (float) $i->quantity,
                'unit_refund_price' => (float) $i->unit_refund_price,
                'subtotal'          => (float) $i->subtotal,
            ])->values()->all(),
        ];
    }

    private static function customerPayment(string $uuid): ?array
    {
        $p = CustomerPayment::with(['user', 'customer', 'shift'])->where('uuid', $uuid)->first();

        return $p ? [
            'customer_uuid'  => $p->customer?->uuid,
            'shift_uuid'     => $p->shift?->uuid,
            'user'           => static::user($p->user),
            'amount'         => (float) $p->amount,
            'payment_method' => $p->payment_method,
            'reference_no'   => $p->reference_no,
            'notes'          => $p->notes,
            'created_at'     => $p->created_at?->toIso8601String(),
        ] : null;
    }

    private static function expense(string $uuid): ?array
    {
        $e = Expense::with('user')->where('uuid', $uuid)->first();

        return $e ? [
            'expense_number' => $e->expense_number,
            'category'       => $e->category,
            'amount'         => (float) $e->amount,
            'expense_date'   => (string) $e->getRawOriginal('expense_date'),
            'note'           => $e->note,
            'user'           => static::user($e->user),
            'created_at'     => $e->created_at?->toIso8601String(),
        ] : null;
    }

    private static function stockMovement(string $uuid): ?array
    {
        $m = StockMovement::with(['user', 'productVariant.product.category'])->where('uuid', $uuid)->first();

        return $m ? [
            'variant'       => static::variant($m->productVariant),
            'quantity'      => (float) $m->quantity,
            'balance_after' => (float) $m->balance_after,
            'movement_type' => $m->movement_type,
            'reference_no'  => $m->reference_no,
            'notes'         => $m->notes,
            'user'          => static::user($m->user),
            'created_at'    => $m->created_at?->toIso8601String(),
        ] : null;
    }

    private static function branchStock(string $uuid): ?array
    {
        $b = BranchStock::with('productVariant.product.category')->where('uuid', $uuid)->first();

        return $b ? [
            'variant'        => static::variant($b->productVariant),
            'stock_quantity' => (float) $b->stock_quantity,
        ] : null;
    }

    private static function user(?User $user): ?array
    {
        return $user ? ['username' => $user->username, 'name' => $user->name] : null;
    }

    private static function variant(?ProductVariant $v): ?array
    {
        if (!$v) {
            return null;
        }

        return [
            'sku'             => $v->sku,
            'barcode'         => $v->barcode,
            'size'            => $v->size,
            'colour'          => $v->colour,
            'unit_of_measure' => $v->unit_of_measure,
            'selling_price'   => (float) $v->selling_price,
            'cost_price'      => (float) $v->cost_price,
            'product'         => $v->product?->name,
            'sku_prefix'      => $v->product?->sku_prefix,
            'category'        => $v->product?->category?->name,
        ];
    }
}
