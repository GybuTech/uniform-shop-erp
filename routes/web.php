<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockEntryController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard & Branch Context
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Legacy and standard)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Settings Center
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/business', [SettingsController::class, 'updateBusinessInfo'])->name('settings.business.update');
    Route::post('/settings/branches', [SettingsController::class, 'storeBranch'])->name('settings.branches.store');
    Route::put('/settings/branches/{branch}', [SettingsController::class, 'updateBranch'])->name('settings.branches.update');
    Route::patch('/settings/branches/{branch}/toggle', [SettingsController::class, 'toggleBranch'])->name('settings.branches.toggle');
    Route::delete('/settings/branches/{branch}', [SettingsController::class, 'destroyBranch'])->name('settings.branches.destroy');
    Route::post('/settings/system-time', [SettingsController::class, 'updateSystemTime'])->name('settings.system-time.update');
    Route::post('/settings/system-time/reset', [SettingsController::class, 'resetSystemTime'])->name('settings.system-time.reset');
    Route::post('/settings/switch-account', [SettingsController::class, 'switchAccount'])->name('settings.switch-account');
    Route::delete('/settings/users/{user}', [SettingsController::class, 'deleteUser'])->name('settings.users.destroy');

    // Roles Management
    Route::resource('roles', RoleController::class)->except(['show', 'destroy']);

    // Expenses
    Route::resource('expenses', ExpenseController::class)->except(['show', 'edit', 'update', 'destroy']);

    // Purchases (Supplier Invoices & Procurements)
    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Quotations
    Route::resource('quotations', QuotationController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Stock Transfers
    Route::get('/transfers', [StockTransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [StockTransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [StockTransferController::class, 'store'])->name('transfers.store');
    Route::post('/transfers/{transfer}/confirm', [StockTransferController::class, 'confirmReceipt'])->name('transfers.confirm');

    // Stock Adjustments (Exclusively for Owner & System Administrator)
    Route::get('/adjustments/edit-stock', [StockAdjustmentController::class, 'editStock'])->name('adjustments.edit-stock');
    Route::post('/adjustments/edit-stock', [StockAdjustmentController::class, 'updateStockDirect'])->name('adjustments.update-stock');
    Route::put('/adjustments/variants/{variant}/stock', [StockAdjustmentController::class, 'updateSingleStock'])->name('adjustments.variant-stock');
    Route::resource('adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);

    // Categories (Per-action permission checks)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('permission:view-categories');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create')->middleware('permission:create-categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:create-categories');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit')->middleware('permission:edit-categories');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('permission:edit-categories');
    Route::patch('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:delete-categories');

    // Products (Per-action permission checks)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index')->middleware('permission:view-products');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create')->middleware('permission:create-products');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('permission:create-products');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('permission:edit-products');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('permission:edit-products');
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('permission:delete-products');
    Route::get('/products-template', [ProductController::class, 'downloadCatalogTemplate'])->name('products.template')->middleware('permission:create-products');
    Route::post('/products-import', [ProductController::class, 'importCatalog'])->name('products.import')->middleware('permission:create-products');
    Route::post('/products-purge-test-catalog', [ProductController::class, 'purgeTestCatalog'])->name('products.purge')->middleware('permission:delete-products');

    // Ajax / Quick Variant Search
    Route::get('/products/variants/search', [ProductVariantController::class, 'searchAjax'])
        ->name('products.variants.search')
        ->middleware('permission:view-products');

    // Quick / Bulk Price Management & Size Tier Pricing
    Route::get('/products/prices', [ProductVariantController::class, 'priceManager'])
        ->name('products.prices')
        ->middleware('permission:edit-product-variants');
    Route::post('/products/prices', [ProductVariantController::class, 'batchUpdateGlobal'])
        ->name('products.prices.update')
        ->middleware('permission:edit-product-variants');
    Route::post('/products/prices/by-size', [ProductVariantController::class, 'updateBySize'])
        ->name('products.prices.by-size')
        ->middleware('permission:edit-product-variants');
    Route::post('/products/prices/groups', [ProductVariantController::class, 'updateByPriceGroups'])
        ->name('products.prices.groups')
        ->middleware('permission:edit-product-variants');
    Route::post('/products/prices/matrix', [ProductVariantController::class, 'updateCategorySizeMatrix'])
        ->name('products.prices.matrix')
        ->middleware('permission:edit-product-variants');
    Route::put('/products/variants/{variant}/price', [ProductVariantController::class, 'updateSinglePrice'])
        ->name('products.variants.price.update')
        ->middleware('permission:edit-product-variants');

    // Variants & Price Tier Manager
    Route::get('/products/{product}/variants/bulk-create', [ProductVariantController::class, 'bulkCreate'])
        ->name('products.variants.bulk-create')
        ->middleware('permission:create-product-variants');
    Route::post('/products/{product}/variants/bulk-store', [ProductVariantController::class, 'bulkStore'])
        ->name('products.variants.bulk-store')
        ->middleware('permission:create-product-variants');
    Route::post('/products/{product}/variants/batch-price-update', [ProductVariantController::class, 'batchPriceUpdate'])
        ->name('products.variants.batch-price-update')
        ->middleware('permission:edit-product-variants');

    // Product Variants Resource
    Route::get('/products/{product}/variants', [ProductVariantController::class, 'index'])->name('products.variants.index')->middleware('permission:view-product-variants');
    Route::get('/products/{product}/variants/create', [ProductVariantController::class, 'create'])->name('products.variants.create')->middleware('permission:create-product-variants');
    Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store')->middleware('permission:create-product-variants');
    Route::get('/products/{product}/variants/{variant}/edit', [ProductVariantController::class, 'edit'])->name('products.variants.edit')->middleware('permission:edit-product-variants');
    Route::put('/products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update')->middleware('permission:edit-product-variants');
    Route::patch('/products/{product}/variants/{variant}', [ProductVariantController::class, 'update']);
    Route::delete('/products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy')->middleware('permission:delete-product-variants');

    // Three Unified Inventory Inward Doorways
    // Doorway 1: Opening Stock Intake
    Route::get('/admin/inventory/opening-stock', [\App\Http\Controllers\InventoryWorkflowController::class, 'openingStockForm'])
        ->name('inventory.opening-stock')
        ->middleware('permission:create-stock-entries');
    Route::post('/admin/inventory/opening-stock', [\App\Http\Controllers\InventoryWorkflowController::class, 'storeOpeningStock'])
        ->name('inventory.opening-stock.store')
        ->middleware('permission:create-stock-entries');

    // Doorway 3: Factory Finished Receipts (Workshop Finished Goods)
    Route::get('/admin/inventory/factory-receipts', [\App\Http\Controllers\InventoryWorkflowController::class, 'factoryReceiptForm'])
        ->name('inventory.factory-receipts')
        ->middleware('permission:create-stock-entries');
    Route::post('/admin/inventory/factory-receipts', [\App\Http\Controllers\InventoryWorkflowController::class, 'storeFactoryReceipt'])
        ->name('inventory.factory-receipts.store')
        ->middleware('permission:create-stock-entries');

    // Doorway 2: Supplier Purchases Admin Route Alias
    Route::get('/admin/purchases', fn() => redirect()->route('purchases.index'))
        ->name('admin.purchases');


    // Stock Entries (Intake & Recalculating Corrections)
    Route::get('/stock-entries', [StockEntryController::class, 'index'])->name('stock-entries.index')->middleware('permission:view-stock-entries');
    Route::get('/stock-entries/import', [StockEntryController::class, 'importForm'])->name('stock-entries.import')->middleware('permission:create-stock-entries');
    Route::get('/stock-entries/template', [StockEntryController::class, 'downloadTemplate'])->name('stock-entries.template')->middleware('permission:create-stock-entries');
    Route::post('/stock-entries/import', [StockEntryController::class, 'import'])->name('stock-entries.import.store')->middleware('permission:create-stock-entries');
    Route::post('/stock-entries/reset-all', [StockEntryController::class, 'resetAllStock'])->name('stock-entries.reset')->middleware('permission:create-stock-entries');
    Route::get('/stock-entries/create', [StockEntryController::class, 'create'])->name('stock-entries.create')->middleware('permission:create-stock-entries');
    Route::post('/stock-entries', [StockEntryController::class, 'store'])->name('stock-entries.store')->middleware('permission:create-stock-entries');
    Route::get('/stock-entries/{stock_entry}', [StockEntryController::class, 'show'])->name('stock-entries.show')->middleware('permission:view-stock-entries');
    Route::get('/stock-entries/{stock_entry}/edit', [StockEntryController::class, 'edit'])->name('stock-entries.edit')->middleware('permission:edit-stock-entries');
    Route::put('/stock-entries/{stock_entry}', [StockEntryController::class, 'update'])->name('stock-entries.update')->middleware('permission:edit-stock-entries');
    Route::patch('/stock-entries/{stock_entry}', [StockEntryController::class, 'update']);
    Route::delete('/stock-entries/{stock_entry}', [StockEntryController::class, 'destroy'])->name('stock-entries.destroy')->middleware('permission:delete-stock-entries');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index')->middleware('permission:view-customers');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create')->middleware('permission:create-customers');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store')->middleware('permission:create-customers');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show')->middleware('permission:view-customers');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit')->middleware('permission:edit-customers');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update')->middleware('permission:edit-customers');
    Route::patch('/customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy')->middleware('permission:delete-customers');

    // POS Terminal
    Route::get('/pos', [PosController::class, 'index'])
        ->name('pos.index')
        ->middleware('permission:create-sales');
    Route::get('/pos/search', [PosController::class, 'search'])
        ->name('pos.search')
        ->middleware('permission:create-sales');
    Route::post('/pos', [PosController::class, 'store'])
        ->name('pos.store')
        ->middleware('permission:create-sales');
    Route::post('/pos/quick-customer', [PosController::class, 'quickCustomer'])
        ->name('pos.quick-customer')
        ->middleware('permission:create-sales');
    Route::post('/pos/quotation', [PosController::class, 'createQuotation'])
        ->name('pos.quotation')
        ->middleware('permission:create-sales');
    Route::get('/pos/recent-sales', [PosController::class, 'recentSales'])
        ->name('pos.recent-sales')
        ->middleware('permission:create-sales');
    Route::get('/pos/receipt', [PosController::class, 'receipt'])
        ->name('pos.receipt')
        ->middleware('permission:create-sales');

    // Cashier Shifts & Cash Rollover Continuity
    Route::get('/shifts', [ShiftController::class, 'index'])
        ->name('shifts.index')
        ->middleware('permission:view-sales');
    Route::get('/shifts/open', [ShiftController::class, 'openForm'])
        ->name('shifts.open')
        ->middleware('permission:create-sales');
    Route::get('/admin/shifts/open', [ShiftController::class, 'openForm'])
        ->name('backend.admin.shifts.open')
        ->middleware('permission:create-sales');
    Route::post('/shifts/open', [ShiftController::class, 'storeOpen'])
        ->name('shifts.open.store')
        ->middleware('permission:create-sales');
    Route::post('/admin/shifts/open', [ShiftController::class, 'storeOpen'])
        ->middleware('permission:create-sales');
    Route::get('/shifts/close', [ShiftController::class, 'closeForm'])
        ->name('shifts.close.form')
        ->middleware('permission:create-sales');
    Route::get('/admin/shifts/close', [ShiftController::class, 'closeForm'])
        ->middleware('permission:create-sales');
    Route::post('/shifts/close', [ShiftController::class, 'close'])
        ->name('shifts.close')
        ->middleware('permission:create-sales');
    Route::post('/shifts/close/store', [ShiftController::class, 'close'])
        ->name('shifts.close.store')
        ->middleware('permission:create-sales');
    Route::post('/admin/shifts/close', [ShiftController::class, 'close'])
        ->middleware('permission:create-sales');
    Route::get('/shifts/current', [ShiftController::class, 'current'])
        ->name('shifts.current')
        ->middleware('permission:create-sales');
    Route::get('/shifts/{shift}/z-report', [ShiftController::class, 'zReport'])
        ->name('shifts.zReport')
        ->middleware('permission:view-sales');

    // Sales Log
    Route::get('/sales', [PosController::class, 'salesIndex'])
        ->name('sales.index')
        ->middleware('permission:view-sales');
    Route::get('/sales/{sale}', [PosController::class, 'salesShow'])
        ->name('sales.show')
        ->middleware('permission:view-sales');

    // Four Independent Reports
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index')
        ->middleware('permission:view-reports');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])
        ->name('reports.profit-loss')
        ->middleware('permission:view-reports');
    Route::get('/reports/sales-trends', [ReportController::class, 'salesTrends'])
        ->name('reports.sales-trends')
        ->middleware('permission:view-reports');
    Route::get('/reports/expenses', [ReportController::class, 'expenses'])
        ->name('reports.expenses')
        ->middleware('permission:view-reports');
    Route::get('/reports/tax', [ReportController::class, 'tax'])
        ->name('reports.tax')
        ->middleware('permission:view-reports');

    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('permission:view-user-management');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:create-users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('permission:create-users');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:edit-users');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:edit-users');
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status')
        ->middleware('permission:edit-users');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy')
        ->middleware('permission:delete-users');

});

require __DIR__.'/auth.php';