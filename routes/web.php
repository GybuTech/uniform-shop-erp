<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', \App\Http\Controllers\CategoryController::class)
        ->except(['show'])
        ->middleware([
            'index' => 'permission:view-categories',
            'create' => 'permission:create-categories',
            'store' => 'permission:create-categories',
            'edit' => 'permission:edit-categories',
            'update' => 'permission:edit-categories',
            'destroy' => 'permission:delete-categories',
        ]);
});

require __DIR__.'/auth.php';