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

    // Categories & Products
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);

    // Variants (Single & Bulk Matrix Creator)
    Route::get('/products/{product}/variants/bulk-create', [ProductVariantController::class, 'bulkCreate'])->name('products.variants.bulk-create');
    Route::post('/products/{product}/variants/bulk-store', [ProductVariantController::class, 'bulkStore'])->name('products.variants.bulk-store');
    Route::resource('products.variants', ProductVariantController::class)->except(['show']);

    // Stock Intake (Finished Goods)
    Route::resource('stock-entries', StockEntryController::class)->only(['index', 'create', 'store', 'show']);

    // Customers
    Route::resource('customers', CustomerController::class);

    // POS Terminal & Sales Log
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/receipt', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('/sales', [PosController::class, 'salesIndex'])->name('sales.index');
    Route::get('/sales/{sale}', [PosController::class, 'salesShow'])->name('sales.show');

    // Analytics & Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Staff / Users Management
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

require __DIR__.'/auth.php';