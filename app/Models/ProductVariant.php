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
        'stock_quantity',
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
     * Get stock for a specific branch (or aggregate total across all branches if branch is null).
     */
    public function getStockForBranch(?string $branchName = null): float
    {
        if (empty($branchName)) {
            $total = (float) $this->branchStocks()->sum('stock_quantity');
            return $total > 0 ? $total : (float) $this->stock_quantity;
        }

        $bs = $this->branchStocks()
            ->where('branch_name', trim($branchName))
            ->first();

        return $bs ? (float) $bs->stock_quantity : 0.00;
    }

    /**
     * Set exact stock for a specific branch.
     */
    public function setStockForBranch(string $branchName, float $quantity): BranchStock
    {
        $branch = Branch::where('name', trim($branchName))->first()
            ?? Branch::firstOrCreate(['name' => trim($branchName)], ['code' => 'BR-' . rand(10, 99)]);

        $bs = BranchStock::updateOrCreate(
            [
                'branch_id'          => $branch->id,
                'product_variant_id' => $this->id,
            ],
            [
                'branch_name'        => $branch->name,
                'stock_quantity'     => max(0, $quantity),
            ]
        );

        // Keep aggregate variant stock_quantity synchronized
        $this->update(['stock_quantity' => $this->branchStocks()->sum('stock_quantity')]);

        return $bs;
    }

    /**
     * Adjust stock for a specific branch (increment or decrement).
     */
    public function adjustStockForBranch(string $branchName, float $diff): BranchStock
    {
        $branch = Branch::where('name', trim($branchName))->first()
            ?? Branch::firstOrCreate(['name' => trim($branchName)], ['code' => 'BR-' . rand(10, 99)]);

        $bs = BranchStock::firstOrCreate(
            [
                'branch_id'          => $branch->id,
                'product_variant_id' => $this->id,
            ],
            [
                'branch_name'        => $branch->name,
                'stock_quantity'     => 0.00,
            ]
        );

        $newQty = max(0, (float)$bs->stock_quantity + $diff);
        $bs->update(['stock_quantity' => $newQty]);

        // Keep aggregate variant stock_quantity synchronized
        $this->update(['stock_quantity' => $this->branchStocks()->sum('stock_quantity')]);

        return $bs;
    }
}