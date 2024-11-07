<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\GoalsController;
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
    Route::resource('settings', SettingsController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::resource('transactions', TransactionController::class);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('stats', [StatsController::class, 'index']);
    Route::resource('goals', GoalsController::class);
    Route::patch('goals/{goal}/progress', [GoalsController::class, 'updateProgress']);
});

Route::post('register', [RegisteredUserController::class, 'store']);
Route::post('login', [AuthenticatedSessionController::class, 'store']);

