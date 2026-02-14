<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PolarSubscriptionController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ServeyController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

///web routes
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/user/activity', [TransactionController::class, 'userActivity'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'apiLogout']);

    Route::controller(TransactionController::class)->group(function () {
        Route::get('transactions/search', 'search');
        Route::get('transactions/paginated', 'paginated');
    });
    
    //Load Stats
    Route::get('transactions/stats', [StatsController::class, 'showStats']);
    Route::get('/stats/yearly-comparison', [StatsController::class, 'yearlyComparison']);
    // Subscription Routes
    // Route::prefix('subscription')->group(function () {
    //     Route::post('checkout', [SubscriptionController::class, 'createCheckoutSession']);
    //     Route::get('status', [SubscriptionController::class, 'getSubscriptionStatus']);
    //     Route::post('verify-session', [SubscriptionController::class, 'verifyCheckoutSession']);
    //     Route::post('cancel', [SubscriptionController::class, 'cancelSubscription']);
    //     Route::post('resume', [SubscriptionController::class, 'resumeSubscription']);
    //     Route::post('payment-method', [SubscriptionController::class, 'updatePaymentMethod']);
    //     Route::get('history', [SubscriptionController::class, 'history']);
    // });

    Route::get('transactions/activity-bar-data-v2', [TransactionController::class, 'activityBarDataV2']);
    Route::get('transactions/daily-bar-data', [TransactionController::class, 'dailyBarData']);
    Route::get('transactions/export', [ExportController::class, 'exportTransactions']);
    Route::get('export/status', [ExportController::class, 'getExportStatus']);
    Route::delete('user-setttings/transactions/reset', [SettingsController::class, 'deleteTransactions']);
    
    // Soft delete routes for transactions
    Route::get('transactions/trashed', [TransactionController::class, 'trashed']);
    Route::post('transactions/{id}/restore', [TransactionController::class, 'restore']);
    Route::delete('transactions/{id}/force', [TransactionController::class, 'forceDestroy']);
    
    Route::resource('settings', SettingsController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::resource('transactions', TransactionController::class);
    
    Route::resource('recurring-expenses', RecurringExpenseController::class);
    Route::get('recurring-expenses-suggestions', [RecurringExpenseController::class, 'suggestions']);
    Route::get('recurring-expenses/{recurringExpense}/loan-details', [RecurringExpenseController::class, 'getLoanDetails']);
    Route::get('stats', [StatsController::class, 'index']);
    Route::resource('goals', GoalsController::class);
    Route::patch('goals/{goal}/progress', [GoalsController::class, 'updateProgress']);
    Route::resource('feedback', FeedbackController::class);

    Route::get('dashboard',DashboardController::class)->name('dashboard');

    // Structured Routes
    Route::get('get-transactions', [TransactionController::class, 'getTransactions']);
    Route::resource('chat', ChatController::class)->only(['index', 'store']);

    //premium features
    Route::resource('categories', CategoryController::class);
    Route::get('user-categories', [CategoryController::class, 'getUserCategories']);

    // Survey route
    Route::post('user/survey', [ServeyController::class, 'store']);
    
    // Import routes
    Route::prefix('import')->group(function () {
        Route::post('upload', [ImportController::class, 'uploadFile']);
        Route::post('mappings', [ImportController::class, 'saveMappings']);
        Route::get('mappings', [ImportController::class, 'getMappings']);
        Route::delete('mappings/{id}', [ImportController::class, 'deleteMapping']);
        Route::post('transactions', [ImportController::class, 'importTransactions']);
    });

    //Polar Subscription Routes
    Route::post('polar/webhook', [PolarSubscriptionController::class, 'handleWebhook']);
    Route::prefix('polar/subscription')->group(function () {
        Route::post('verify-session', [PolarSubscriptionController::class, 'verifyCheckoutSession']);
        Route::post('cancel', [PolarSubscriptionController::class, 'cancelSubscription']);
        Route::get('status', [PolarSubscriptionController::class, 'getSubscriptionStatus']);
        Route::get('history', [PolarSubscriptionController::class, 'index']);
    });


//    Route::resource('user-wallets', WalletController::class);
    Route::prefix('wallets')->group(function () {
        Route::get('/', [WalletController::class, 'index']);
        Route::post('/', [WalletController::class, 'store']);
        Route::get('/{wallet}', [WalletController::class, 'show']);
        Route::put('/{wallet}', [WalletController::class, 'update']);
        Route::delete('/{wallet}', [WalletController::class, 'destroy']);
        Route::get('/{wallet}/transactions', [WalletController::class, 'transactions']);
        Route::post('/transfer', [WalletController::class, 'transfer']);
        Route::get('/{wallet}/balance-history', [WalletController::class, 'balanceHistory']);
    });

});

// Paddle webhook route (no auth middleware)
//Route::post('paddle/webhook', [SubscriptionController::class, 'handleWebhook']);

Route::post('register', [RegisteredUserController::class, 'store']);
Route::post('login', [AuthenticatedSessionController::class, 'store']);

// OTP Authentication Routes
Route::post('auth/send-otp', [AuthenticatedSessionController::class, 'sendOtp']);
Route::post('auth/verify-otp', [AuthenticatedSessionController::class, 'verifyOtp']);

// Google OAuth (token exchange happens server-side for security)
Route::post('auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback']);
