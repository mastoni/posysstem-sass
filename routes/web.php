<?php

use App\Http\Controllers\SSOController;
use App\Http\Controllers\StoreSetupController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sso', [SSOController::class, 'handle'])->name('sso');

// Internal API for Billing Integration
Route::get('/api/internal/stats', [\App\Http\Controllers\Api\ExternalStatsController::class, 'getStats']);

// Headless POS API for PWA Integration
Route::prefix('api/internal/pos')->group(function () {
    Route::get('/products', [\App\Http\Controllers\Api\PosInternalApiController::class, 'getProducts']);
    Route::get('/categories', [\App\Http\Controllers\Api\PosInternalApiController::class, 'getCategories']);
    Route::post('/checkout', [\App\Http\Controllers\Api\PosInternalApiController::class, 'checkout']);
    Route::get('/reports', [\App\Http\Controllers\Api\PosInternalApiController::class, 'getFullReports']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/setup-store', [StoreSetupController::class, 'index'])->name('store.setup');
    Route::post('/setup-store', [StoreSetupController::class, 'store'])->name('store.setup.save');
});
