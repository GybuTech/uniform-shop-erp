<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory, \App\Models\Concerns\Syncable;

    /** Record type used when uploading to HQ (see App\Sync). */
    public const SYNC_TYPE = 'sale';

    protected $fillable = [
        'sale_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'cost_price',
        'line_total',
        // Set explicitly from SystemTime (the business clock) when a sale is recorded
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class)->withTrashed();
    }

    public function variant(): BelongsTo
    {
        return $this->productVariant();
    }

    /** Sale lines are uploaded as part of their sale. */
    public function syncTarget(): array
    {
        return [Sale::SYNC_TYPE, Sale::whereKey($this->sale_id)->value('uuid')];
    }
}
