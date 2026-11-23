<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

// Shop PCs: upload queued changes to HQ every minute whenever online (no-op until configured)
Schedule::command('sync:run')->everyMinute()->withoutOverlapping(10)->runInBackground();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('catalog:purge-test-data {--with-categories : Also delete all categories}', function () {
    $purgeCategories = $this->option('with-categories');

    $this->warn('=======================================================');
    $this->warn('  GYBUTECH POS: PURGING TEST CATALOG & INVENTORY');
    $this->warn('=======================================================');

    DB::transaction(function () use ($purgeCategories) {
        // 1. Wipe dependent stock and transaction records
        \App\Models\BranchStock::query()->delete();
        \App\Models\StockEntryItem::query()->delete();
        \App\Models\StockEntry::query()->delete();
        \App\Models\StockAdjustmentItem::query()->delete();
        \App\Models\StockAdjustment::query()->delete();
        \App\Models\StockTransferItem::query()->delete();
        \App\Models\StockTransfer::query()->delete();
        \App\Models\PurchaseItem::query()->delete();
        \App\Models\Purchase::query()->delete();
        \App\Models\SaleItem::query()->delete();
        \App\Models\Sale::query()->delete();

        // 2. Wipe product variants & products
        \App\Models\ProductVariant::withTrashed()->forceDelete();
        \App\Models\Product::withTrashed()->forceDelete();

        if ($purgeCategories) {
            \App\Models\Category::query()->delete();
        }
    });

    $this->info('✓ All test products, variants, prices, stock, and transactions removed.');
    if ($purgeCategories) {
        $this->info('✓ All categories removed.');
    } else {
        $this->line('ℹ Categories preserved.');
    }
    $this->info('✓ Branches, Users, Roles, and Business Settings are 100% intact.');
    $this->info('Your system is now completely clean and ready for real catalog entry!');
})->purpose('Purge all test products, variants, selling prices, and test transactions');
