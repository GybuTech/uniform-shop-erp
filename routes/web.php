<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockEntryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('products.variants', ProductVariantController::class)->except(['show']);
    Route::resource('stock-entries', StockEntryController::class)->only(['index', 'create', 'store']);
    Route::resource('customers', CustomerController::class);

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/receipt', [PosController::class, 'receipt'])->name('pos.receipt');

});

require __DIR__.'/auth.php';