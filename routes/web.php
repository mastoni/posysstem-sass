<?php

use App\Http\Controllers\SSOController;
use App\Http\Controllers\StoreSetupController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sso', [SSOController::class, 'handle'])->name('sso');

// Internal API for Billing Integration
Route::get('/api/internal/stats', [\App\Http\Controllers\Api\ExternalStatsController::class, 'getStats']);

use App\Http\Controllers\Api\PosInternalApiController;

// Headless POS API for PWA Integration
Route::prefix('api/internal/pos')->group(function () {
    Route::get('/products', [PosInternalApiController::class, 'getProducts']);
    Route::post('/products', [PosInternalApiController::class, 'storeProduct']);
    Route::post('/products/{id}', [PosInternalApiController::class, 'updateProduct']);
    Route::delete('/products/{id}', [PosInternalApiController::class, 'deleteProduct']);
    
    Route::get('/categories', [PosInternalApiController::class, 'getCategories']);
    Route::post('/categories', [PosInternalApiController::class, 'storeCategory']);
    Route::post('/categories/{id}', [PosInternalApiController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [PosInternalApiController::class, 'deleteCategory']);

    Route::post('/checkout', [PosInternalApiController::class, 'checkout']);
    Route::get('/orders', [PosInternalApiController::class, 'getOrders']);
    Route::post('/update-store', [PosInternalApiController::class, 'updateStore']);
    Route::get('/reports', [PosInternalApiController::class, 'getFullReports']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/setup-store', [StoreSetupController::class, 'index'])->name('store.setup');
    Route::post('/setup-store', [StoreSetupController::class, 'store'])->name('store.setup.save');
});
