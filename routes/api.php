<?php

use App\Http\Controllers\Api\SyncController;
use App\Http\Middleware\AuthenticateSyncKey;
use Illuminate\Support\Facades\Route;

/*
| Shop → HQ sync (HQ / cloud mode only). Shops authenticate with their sync key.
*/
Route::prefix('sync')->middleware([AuthenticateSyncKey::class, 'throttle:120,1'])->group(function () {
    Route::get('/ping', [SyncController::class, 'ping'])->name('sync.ping');
    Route::post('/push', [SyncController::class, 'push'])->name('sync.push');
});
