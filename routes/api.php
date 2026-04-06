<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\SaleApiController;
use App\Http\Controllers\Api\DashboardApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthApiController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Dashboard Stats
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);

    // Products
    Route::get('/products', [ProductApiController::class, 'index']);
    Route::get('/products/{product}', [ProductApiController::class, 'show']);

    // Sales
    Route::get('/sales', [SaleApiController::class, 'index']);
    Route::get('/sales/{sale}', [SaleApiController::class, 'show']);
    Route::post('/sales', [SaleApiController::class, 'store']);
});

