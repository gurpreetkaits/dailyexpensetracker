<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view('home');
});

Route::get('posts', [BlogController::class, 'index'])->name('posts');
Route::get('post/{slug}', [BlogController::class, 'show']);

Route::get('pricing', function () {
    return view('pricing');
})->name('pricing');

Route::middleware(['auth'])->group(function () {
    Route::post('subscription/checkout', [SubscriptionController::class, 'createCheckoutSession'])->name('subscription.checkout');
    Route::get('subscription/success', [SubscriptionController::class, 'handleSubscriptionSuccess'])->name('subscription.success');
    Route::get('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('subscription/cancel-subscription', [SubscriptionController::class, 'cancelSubscription'])->name('subscription.cancel-subscription');
    Route::post('subscription/resume', [SubscriptionController::class, 'resumeSubscription'])->name('subscription.resume');
});

Route::controller(ToolsController::class)->prefix('tools')->group(function () {
    Route::get('/', 'index')->name('tools.index');
    Route::get('/simple-interest-calculator', 'simpleInterestCalculator')->name('tools.simple-interest-calculator');
    Route::get('sip-calculator', 'sipCalculator')->name('tools.sip-calculator');
});
