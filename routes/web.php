<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockEntryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Categories
    Route::resource('categories', CategoryController::class)
        ->except(['show'])
        ->middleware([
            'index'   => 'permission:view-categories',
            'create'  => 'permission:create-categories',
            'store'   => 'permission:create-categories',
            'edit'    => 'permission:edit-categories',
            'update'  => 'permission:edit-categories',
            'destroy' => 'permission:delete-categories',
        ]);

    // Products
    Route::resource('products', ProductController::class)
        ->except(['show'])
        ->middleware([
            'index'   => 'permission:view-products',
            'create'  => 'permission:create-products',
            'store'   => 'permission:create-products',
            'edit'    => 'permission:edit-products',
            'update'  => 'permission:edit-products',
            'destroy' => 'permission:delete-products',
        ]);

    // Variants
    Route::get('/products/{product}/variants/bulk-create', [ProductVariantController::class, 'bulkCreate'])
        ->name('products.variants.bulk-create')
        ->middleware('permission:create-product-variants');
    Route::post('/products/{product}/variants/bulk-store', [ProductVariantController::class, 'bulkStore'])
        ->name('products.variants.bulk-store')
        ->middleware('permission:create-product-variants');
    Route::resource('products.variants', ProductVariantController::class)
        ->except(['show'])
        ->middleware([
            'index'   => 'permission:view-product-variants',
            'create'  => 'permission:create-product-variants',
            'store'   => 'permission:create-product-variants',
            'edit'    => 'permission:edit-product-variants',
            'update'  => 'permission:edit-product-variants',
            'destroy' => 'permission:delete-product-variants',
        ]);

    // Stock Entries (Intake & Recalculating Corrections)
    Route::resource('stock-entries', StockEntryController::class)
        ->middleware([
            'index'   => 'permission:view-stock-entries',
            'create'  => 'permission:create-stock-entries',
            'store'   => 'permission:create-stock-entries',
            'show'    => 'permission:view-stock-entries',
            'edit'    => 'permission:edit-stock-entries',
            'update'  => 'permission:edit-stock-entries',
            'destroy' => 'permission:delete-stock-entries',
        ]);

    // Customers
    Route::resource('customers', CustomerController::class)
        ->middleware([
            'index'   => 'permission:view-customers',
            'create'  => 'permission:create-customers',
            'store'   => 'permission:create-customers',
            'show'    => 'permission:view-customers',
            'edit'    => 'permission:edit-customers',
            'update'  => 'permission:edit-customers',
            'destroy' => 'permission:delete-customers',
        ]);

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
    Route::get('/pos/receipt', [PosController::class, 'receipt'])
        ->name('pos.receipt')
        ->middleware('permission:create-sales');

    // Sales Log
    Route::get('/sales', [PosController::class, 'salesIndex'])
        ->name('sales.index')
        ->middleware('permission:view-sales');
    Route::get('/sales/{sale}', [PosController::class, 'salesShow'])
        ->name('sales.show')
        ->middleware('permission:view-sales');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index')
        ->middleware('permission:view-reports');

    // Users Management
    Route::resource('users', UserController::class)
        ->except(['show', 'destroy'])
        ->middleware([
            'index'  => 'permission:view-user-management',
            'create' => 'permission:create-users',
            'store'  => 'permission:create-users',
            'edit'   => 'permission:edit-users',
            'update' => 'permission:edit-users',
        ]);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status')
        ->middleware('permission:edit-users');

});

require __DIR__.'/auth.php';