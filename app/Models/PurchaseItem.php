<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_variant_id',
        'item_name',
        'unit',
        'unit_cost',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'subtotal'  => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class)->withTrashed();
    }

    /**
     * Get human-readable item title for display in receipts and lists.
     * Works for both raw materials (elastics, fabric, thread) and finished uniform variants.
     */
    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->item_name)) {
            return $this->item_name;
        }

        if ($this->productVariant) {
            $productName = $this->productVariant->product?->name ?? 'Uniform';
            return "{$productName} ({$this->productVariant->sku}) - Size {$this->productVariant->size}";
        }

        return 'Procurement Item';
    }
}
