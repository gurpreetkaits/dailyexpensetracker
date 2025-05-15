<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ServeyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    //Load Stats
    Route::get('transactions/stats', [StatsController::class, 'showStats']);

    // Subscription Routes
    Route::prefix('subscription')->group(function () {
        Route::post('checkout', [SubscriptionController::class, 'createCheckoutSession']);
        Route::get('status', [SubscriptionController::class, 'getSubscriptionStatus']);
        Route::post('verify-session', [SubscriptionController::class, 'verifyCheckoutSession']);
        Route::post('cancel', [SubscriptionController::class, 'cancelSubscription']);
        Route::post('resume', [SubscriptionController::class, 'resumeSubscription']);
        Route::post('payment-method', [SubscriptionController::class, 'updatePaymentMethod']);
        Route::get('history', [SubscriptionController::class, 'history']);
    });

    Route::delete('user-setttings/transactions/reset', [SettingsController::class, 'deleteTransactions']);
    Route::resource('settings', SettingsController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::resource('transactions', TransactionController::class);
    Route::controller(TransactionController::class)->group(function () {
        Route::get('transactions/search', [TransactionController::class, 'search']);
    });
    Route::resource('recurring-expenses', RecurringExpenseController::class);
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

});

// Paddle webhook route (no auth middleware)
//Route::post('paddle/webhook', [SubscriptionController::class, 'handleWebhook']);

Route::post('register', [RegisteredUserController::class, 'store']);
Route::post('login', [AuthenticatedSessionController::class, 'store']);

