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
        Route::get('/reports/profit-loss', [\App\Http\Controllers\Api\V1\ReportController::class, 'getProfitLoss']);
        Route::get('/reports/purchase-sell', [\App\Http\Controllers\Api\V1\ReportController::class, 'getPurchaseSell']);
        Route::get('/reports/tax', [\App\Http\Controllers\Api\V1\ReportController::class, 'getTaxReport']);
        Route::get('/reports/expense', [\App\Http\Controllers\Api\V1\ReportController::class, 'getExpenseReport']);
        Route::get('/reports/register', [\App\Http\Controllers\Api\V1\ReportController::class, 'getRegisterReport']);
        Route::get('/reports/stock', [\App\Http\Controllers\Api\V1\ReportController::class, 'getStockReport']);
        Route::get('/reports/stock-adjustment', [\App\Http\Controllers\Api\V1\ReportController::class, 'getStockAdjustmentReport']);
        Route::get('/reports/stock-expiry', [\App\Http\Controllers\Api\V1\ReportController::class, 'getStockExpiryReport']);
        Route::get('/reports/lot', [\App\Http\Controllers\Api\V1\ReportController::class, 'getLotReport']);
        Route::get('/reports/stock-value', [\App\Http\Controllers\Api\V1\ReportController::class, 'getStockValue']);
        Route::get('/reports/trending-products', [\App\Http\Controllers\Api\V1\ReportController::class, 'getTrendingProducts']);
        Route::get('/reports/product-purchase', [\App\Http\Controllers\Api\V1\ReportController::class, 'getProductPurchaseReport']);
        Route::get('/reports/product-sell', [\App\Http\Controllers\Api\V1\ReportController::class, 'getProductSellReport']);
        Route::get('/reports/product-sell-grouped', [\App\Http\Controllers\Api\V1\ReportController::class, 'getProductSellGroupedReport']);
        Route::get('/reports/items', [\App\Http\Controllers\Api\V1\ReportController::class, 'itemsReport']);
        Route::get('/reports/customer-supplier', [\App\Http\Controllers\Api\V1\ReportController::class, 'getCustomerSuppliers']);
        Route::get('/reports/customer-group', [\App\Http\Controllers\Api\V1\ReportController::class, 'getCustomerGroup']);
        Route::get('/reports/sales-representative', [\App\Http\Controllers\Api\V1\ReportController::class, 'getSalesRepresentativeReport']);
        Route::get('/reports/service-staff', [\App\Http\Controllers\Api\V1\ReportController::class, 'getServiceStaffReport']);
        Route::get('/reports/purchase-payment', [\App\Http\Controllers\Api\V1\ReportController::class, 'purchasePaymentReport']);
        Route::get('/reports/sell-payment', [\App\Http\Controllers\Api\V1\ReportController::class, 'sellPaymentReport']);
        Route::get('/reports/balance-sheet', [\App\Http\Controllers\Api\V1\ReportController::class, 'balanceSheet']);
        Route::get('/reports/trial-balance', [\App\Http\Controllers\Api\V1\ReportController::class, 'trialBalance']);
        Route::get('/reports/payment-account', [\App\Http\Controllers\Api\V1\ReportController::class, 'paymentAccountReport']);
        Route::get('/reports/table', [\App\Http\Controllers\Api\V1\ReportController::class, 'getTableReport']);
        Route::get('/reports/activity-log', [\App\Http\Controllers\Api\V1\ReportController::class, 'activityLog']);
        Route::get('/reports/gst-sales', [\App\Http\Controllers\Api\V1\ReportController::class, 'gstSalesReport']);
        Route::get('/reports/gst-purchase', [\App\Http\Controllers\Api\V1\ReportController::class, 'gstPurchaseReport']);
        Route::get('/reports/daily-performance', [\App\Http\Controllers\Api\V1\ReportController::class, 'dailyPerformance']);


        // Cash Register
        Route::post('/cash-register/open', [\App\Http\Controllers\Api\V1\CashRegisterController::class, 'open']);
        Route::get('/cash-register/status/{user_id}', [\App\Http\Controllers\Api\V1\CashRegisterController::class, 'status']);
    });
});
