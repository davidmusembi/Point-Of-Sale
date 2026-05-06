<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// V1 API Routes
Route::prefix('v1')->middleware('api.logger')->group(function () {
    // Public routes
    Route::post('/auth/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login']);

    // Protected routes
    Route::middleware('api.v1.auth')->group(function () {
        // Auth
        Route::get('/auth/me', [App\Http\Controllers\Api\V1\AuthController::class, 'me']);

        // Products
        Route::get('/products', [App\Http\Controllers\Api\V1\ProductController::class, 'index']);
        Route::get('/products/{id}', [App\Http\Controllers\Api\V1\ProductController::class, 'show'])->where('id', '[0-9]+');
        Route::get('/products/barcode/{barcode}', [App\Http\Controllers\Api\V1\ProductController::class, 'showByBarcode']);
        Route::get('/categories', [App\Http\Controllers\Api\V1\CategoryController::class, 'index']);

        // Settings
        Route::get('/settings', [App\Http\Controllers\Api\V1\SettingController::class, 'index']);

        // Transactions
        Route::get('/transactions', [App\Http\Controllers\Api\V1\TransactionController::class, 'index']);
        Route::post('/transactions', [App\Http\Controllers\Api\V1\TransactionController::class, 'store']);
        Route::get('/transactions/{id}', [App\Http\Controllers\Api\V1\TransactionController::class, 'show'])->where('id', '[0-9]+');

        // Dashboard
        Route::get('/dashboard/kpis', [App\Http\Controllers\Api\V1\DashboardController::class, 'kpis']);
        Route::get('/dashboard/low-stock-alerts', [App\Http\Controllers\Api\V1\DashboardController::class, 'lowStockAlerts']);
        Route::get('/dashboard/recent-transactions', [App\Http\Controllers\Api\V1\DashboardController::class, 'recentTransactions']);

        // Reports
        Route::get('/reports/sales', [App\Http\Controllers\Api\V1\ReportController::class, 'sales']);
        Route::get('/reports/inventory-analysis', [App\Http\Controllers\Api\V1\ReportController::class, 'inventoryAnalysis']);
        Route::get('/reports/customers', [App\Http\Controllers\Api\V1\ReportController::class, 'customers']);
        Route::get('/reports/daily-performance', [App\Http\Controllers\Api\V1\ReportController::class, 'dailyPerformance']);
        Route::get('/reports/purchases', [App\Http\Controllers\Api\V1\ReportController::class, 'purchases']);
    });
});
