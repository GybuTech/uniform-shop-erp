<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, \App\Models\Concerns\Syncable;

    /** Record type used when uploading to HQ (see App\Sync). */
    public const SYNC_TYPE = 'stock_movement';

    protected $fillable = [
        'branch_id',
        'branch_name',
        'product_variant_id',
        'quantity',
        'balance_after',
        'movement_type',
        'reference_id',
        'reference_no',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity'      => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
