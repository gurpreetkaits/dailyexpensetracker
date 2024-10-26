<?php

use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\SettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('settings',SettingsController::class);
    Route::resource('currencies',CurrencyController::class);
});