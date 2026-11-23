<?php

use App\Models\Branch;
use App\Models\ProductVariant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Older imports stored stock only as one company-wide number per item (product_variants.stock_quantity)
 * and never as per-shop stock (branch_stocks). The till sells from per-shop stock, so on such a PC
 * every item showed 0 and could not be sold.
 *
 * A shop PC holds only its own shop's data, so that number IS this shop's stock: move it into
 * this PC's shop as opening stock (recorded in the stock history). Items that already have per-shop
 * stock are left alone. Runs once; safe to re-run (it only touches items with no per-shop stock).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (config('app.store_mode', 'standalone') !== 'standalone') {
            return; // HQ never guesses which shop a stock figure belongs to
        }

        $legacy = ProductVariant::where('stock_quantity', '>', 0)->whereDoesntHave('branchStocks');
        if (!$legacy->exists()) {
            return;
        }

        $branchName = trim((string) config('app.default_branch', 'Main Shop'));
        $branch = Branch::where('name', $branchName)->first();
        if (!$branch) {
            $known = Branch::orderBy('name')->pluck('name')->join(', ');
            throw new RuntimeException(
                "This PC is set to shop \"{$branchName}\" (STORE_DEFAULT_BRANCH in .env), but no branch has that name. "
                . "Branches: {$known}. Fix STORE_DEFAULT_BRANCH, then start the POS again."
            );
        }

        DB::transaction(function () use ($legacy, $branch) {
            $legacy->orderBy('id')->each(function (ProductVariant $variant) use ($branch) {
                $variant->setStockForBranch(
                    $branch->name,
                    (float) $variant->stock_quantity,
                    'opening_stock',
                    null,
                    'LEGACY-STOCK',
                    'Stock carried over from the earlier system total'
                );
            });
        });
    }

    public function down(): void
    {
        // Nothing to undo: the stock now lives per shop, and the company total is unchanged.
    }
};
