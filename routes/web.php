<?php

use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('/credit-pay', [StripeController::class, 'creditpay'])->name('credit-pay');
Route::get('/googlepay', [StripeController::class, 'googlepay'])->name('googlepay');
Route::post('/payment/intent', [StripeController::class, 'createPaymentIntent'])->name('payment.intent');
