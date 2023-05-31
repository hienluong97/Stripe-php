<?php

use App\Http\Controllers\StripeController;
use App\Http\Controllers\GooglepaymentController;
use Illuminate\Support\Facades\Route;
use Stripe\CreditNote;

// Credit
Route::get('/checkout', [StripeController::class, 'checkout'])->name('checkout');
Route::post('/payment', [StripeController::class, 'payment'])->name('payment');

// googlepay
Route::get('/googlepay', [GooglepaymentController::class, 'index'])->name('googlepay');
Route::post('/payment/intent', [GooglepaymentController::class, 'createPaymentIntent'])->name('payment.intent');
