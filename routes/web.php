<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ToolsController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view('home');
});

Route::get('posts', [BlogController::class, 'index'])->name('posts');
Route::get('post/{slug}', [BlogController::class, 'show']);

Route::get('terms-and-conditions', function () {
    return view('terms');
})->name('terms');

Route::controller(ToolsController::class)->prefix('tools')->group(function () {
    Route::get('/', 'index')->name('tools.index');
    Route::get('/simple-interest-calculator', 'simpleInterestCalculator')->name('tools.simple-interest-calculator');
    Route::get('sip-calculator', 'sipCalculator')->name('tools.sip-calculator');
});

include 'mobile.php';
