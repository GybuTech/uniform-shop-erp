<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'size',
        'colour',
        'unit_of_measure',
        'brand_name',
        'selling_price',
        'cost_price',
        'stock_quantity',
    ];

    protected $casts = [
        'selling_price'  => 'decimal:2',
        'cost_price'     => 'decimal:2',
        'stock_quantity' => 'decimal:2',
    ];

    /**
     * Universal dynamic display title across retail verticals.
     * Uniforms: e.g. "Edelvale Junior Sweater (Size 24 / Navy Blue)"
     * Hardware/Agrovet: e.g. "Bamburi Cement - 50kg Bag" or "Twiga Chemical - 1 Litre"
     */
    public function getFormattedTitleAttribute(): string
    {
        $productName = $this->product?->name ?? 'Product';
        $brandPrefix = (!empty($this->brand_name) && !str_contains(strtolower($productName), strtolower($this->brand_name))) 
            ? "{$this->brand_name} " 
            : '';

        if (!empty($this->size) || (!empty($this->colour) && strtolower($this->colour) !== 'standard')) {
            $details = [];
            if (!empty($this->size)) {
                $details[] = "Size {$this->size}";
            }
            if (!empty($this->colour) && strtolower($this->colour) !== 'standard') {
                $details[] = $this->colour;
            }
            return trim("{$brandPrefix}{$productName}") . ' (' . implode(' / ', $details) . ')';
        }

        $unitSuffix = !empty($this->unit_of_measure) && !in_array(strtolower($this->unit_of_measure), ['piece', 'pcs']) 
            ? " - {$this->unit_of_measure}" 
            : '';

        return trim("{$brandPrefix}{$productName}{$unitSuffix}");
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    /**
     * Single definitive source of shelf inventory.
     */
    public function getStock(): float
    {
        return (float) ($this->stock_quantity ?? 0.00);
    }

    /**
     * Backward-compatible stock accessor returning single store shelf inventory.
     */
    public function getStockForBranch(?string $branchName = null): float
    {
        return $this->getStock();
    }

    /**
     * Update shelf inventory directly on the product variant and sync BranchStock.
     */
    public function setStockForBranch(?string $branchName, float $quantity): self
    {
        $newQty = max(0, $quantity);
        $this->update(['stock_quantity' => $newQty]);

        try {
            $name = $branchName ?: config('app.default_branch', 'Main Shop');
            $branch = Branch::where('name', $name)->first() ?? Branch::first();
            if ($branch) {
                BranchStock::updateOrCreate(
                    ['product_variant_id' => $this->id, 'branch_id' => $branch->id],
                    ['branch_name' => $branch->name, 'stock_quantity' => $newQty]
                );
            }
        } catch (\Throwable) {
            // Non-blocking fallback for resilient offline execution
        }

        return $this;
    }

    /**
     * Adjust shelf inventory directly on the product variant and sync BranchStock.
     */
    public function adjustStockForBranch(?string $branchName, float $diff): self
    {
        $newQty = max(0, (float)$this->stock_quantity + $diff);
        $this->update(['stock_quantity' => $newQty]);

        try {
            $name = $branchName ?: config('app.default_branch', 'Main Shop');
            $branch = Branch::where('name', $name)->first() ?? Branch::first();
            if ($branch) {
                BranchStock::updateOrCreate(
                    ['product_variant_id' => $this->id, 'branch_id' => $branch->id],
                    ['branch_name' => $branch->name, 'stock_quantity' => $newQty]
                );
            }
        } catch (\Throwable) {
            // Non-blocking fallback for resilient offline execution
        }

        return $this;
    }
}