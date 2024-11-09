<?php

use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view('home');
});

Route::get('posts', [BlogController::class, 'index'])->name('posts');
Route::get('post/{slug}', [BlogController::class, 'show']);