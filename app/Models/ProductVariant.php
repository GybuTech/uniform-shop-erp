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
     * Scope to variants currently in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
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
    public function getStockForBranch($branchId = null): float
    {
        return $this->getStock();
    }

    /**
     * Update shelf inventory directly on the product variant without touching branch_stocks.
     */
    public function setStockForBranch($branchId = null, float $quantity = 0.0): self
    {
        $newQty = max(0, $quantity);
        $this->stock_quantity = $newQty;
        $this->save();

        return $this;
    }

    /**
     * Adjust shelf inventory directly on the product variant without touching branch_stocks.
     */
    public function adjustStockForBranch($branchId = null, float $quantity = 0.0, string $type = 'in', ?string $notes = null): self
    {
        if ($type === 'in' || $type === 'add') {
            $diff = abs($quantity);
        } elseif ($type === 'out' || $type === 'subtract') {
            $diff = -abs($quantity);
        } else {
            $diff = $quantity;
        }

        $newQty = max(0, (float)$this->stock_quantity + $diff);
        $this->stock_quantity = $newQty;
        $this->save();

        try {
            if (class_exists(\App\Models\StockMovement::class)) {
                \App\Models\StockMovement::create([
                    'product_variant_id' => $this->id,
                    'quantity'           => abs($diff),
                    'balance_after'      => $newQty,
                    'movement_type'      => $diff >= 0 ? 'adjustment_in' : 'adjustment_out',
                    'user_id'            => auth()->id(),
                    'notes'              => $notes ?? 'Direct stock adjustment',
                ]);
            }
        } catch (\Throwable) {
            // Non-blocking fallback for resilient offline execution
        }

        return $this;
    }

    /**
     * Current stock at a branch, read with a row lock (use inside a DB transaction).
     */
    public function lockedStockForBranch(string $branchName): float
    {
        $bs = BranchStock::where('branch_name', trim($branchName))
            ->where('product_variant_id', $this->id)
            ->lockForUpdate()
            ->first();

        return $bs ? (float) $bs->stock_quantity : 0.00;
    }

    public static function resolveBranch(string $branchName): Branch
    {
        $name = trim($branchName);

        return Branch::where('name', $name)->first()
            ?? Branch::firstOrCreate(['name' => $name], ['code' => 'BR-' . strtoupper(substr(md5($name), 0, 6))]);
    }

    private function lockedBranchStock(Branch $branch, float $initialQty): BranchStock
    {
        $bs = BranchStock::where('branch_id', $branch->id)
            ->where('product_variant_id', $this->id)
            ->lockForUpdate()
            ->first();

        if (!$bs) {
            $bs = BranchStock::create([
                'branch_id'          => $branch->id,
                'branch_name'        => $branch->name,
                'product_variant_id' => $this->id,
                'stock_quantity'     => $initialQty,
            ]);
        } elseif ($bs->branch_name !== $branch->name) {
            // Heal rows left behind by an older branch rename
            $bs->branch_name = $branch->name;
        }

        return $bs;
    }

    private function syncAggregateStock(): void
    {
        $this->update(['stock_quantity' => $this->branchStocks()->sum('stock_quantity')]);
    }

    private function recordMovement(
        Branch $branch,
        float $change,
        float $balanceAfter,
        ?string $movementType,
        ?int $referenceId,
        ?string $referenceNo,
        ?string $notes
    ): void {
        if ($movementType === null || abs($change) < 0.00001) {
            return;
        }

        if ($movementType === 'adjustment') {
            $movementType = $change > 0 ? 'adjustment_gain' : 'adjustment_loss';
        }

        StockMovement::create([
            'branch_id'          => $branch->id,
            'branch_name'        => $branch->name,
            'product_variant_id' => $this->id,
            'quantity'           => round($change, 2),
            'balance_after'      => round($balanceAfter, 2),
            'movement_type'      => $movementType,
            'reference_id'       => $referenceId,
            'reference_no'       => $referenceNo,
            'user_id'            => auth()->id(),
            'notes'              => $notes,
        ]);
    }
}