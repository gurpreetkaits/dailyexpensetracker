<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ToolsController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view('home');
});

Route::get('posts', [BlogController::class, 'index'])->name('posts');
Route::get('post/{slug}', [BlogController::class, 'show']);

Route::controller(ToolsController::class)->prefix('tools')->group(function () {
    Route::get('/simple-interest-calculator', 'index');
});
