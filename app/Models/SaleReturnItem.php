<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItem extends Model
{
    use HasFactory, \App\Models\Concerns\Syncable;

    /** Record type used when uploading to HQ (see App\Sync). */
    public const SYNC_TYPE = 'sale_return';

    protected $fillable = [
        'sale_return_id',
        'product_variant_id',
        'quantity',
        'unit_refund_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity'          => 'decimal:2',
        'unit_refund_price' => 'decimal:2',
        'subtotal'          => 'decimal:2',
    ];

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /** Return lines are uploaded as part of their return. */
    public function syncTarget(): array
    {
        return [SaleReturn::SYNC_TYPE, SaleReturn::whereKey($this->sale_return_id)->value('uuid')];
    }
}
