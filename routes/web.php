<?php

use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('/checkout', [StripeController::class, 'checkout'])->name('checkout');
Route::post('/payment', [StripeController::class, 'payment'])->name('payment');
