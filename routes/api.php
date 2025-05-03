<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    //Load Stats
    Route::get('transactions/stats', [StatsController::class, 'showStats']);
    Route::delete('user-setttings/transactions/reset', [SettingsController::class, 'deleteTransactions']);
    Route::resource('settings', SettingsController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::resource('transactions', TransactionController::class);
    Route::controller(TransactionController::class)->group(function () {
        Route::get('transactions/search', [TransactionController::class, 'search']);
    });
    Route::resource('recurring-expenses', RecurringExpenseController::class);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('stats', [StatsController::class, 'index']);
    Route::resource('goals', GoalsController::class);
    Route::patch('goals/{goal}/progress', [GoalsController::class, 'updateProgress']);
    Route::resource('feedback', FeedbackController::class);
});

Route::post('register', [RegisteredUserController::class, 'store']);
Route::post('login', [AuthenticatedSessionController::class, 'store']);

