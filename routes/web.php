<?php

use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('/credit-pay', [StripeController::class, 'creditpay'])->name('credit-pay');
Route::get('/googlepay', [StripeController::class, 'googlepay'])->name('googlepay');
Route::post('/payment/intent', [StripeController::class, 'createPaymentIntent'])->name('payment.intent');



Route::get('/bankpay', [StripeController::class, 'bankpay'])->name('bankpay');
Route::post('/payment/bank-transfer', [StripeController::class, 'createBankPaymentIntent'])->name('payment.bank-transfer');
Route::post('/payment/bank-transfer-complete', [StripeController::class, 'completeBankPayment'])->name('payment.bank-transfer.complete');



Route::get('/bank-refund', [StripeController::class, 'bankRefund'])->name('bank-refund');
Route::post('/refund/balance', [StripeController::class, 'bankRefundBalance'])->name('bank-refund-balance');
Route::post('/refund/payment', [StripeController::class, 'bankRefundPayment'])->name('bank-refund-payment');


Route::post('/webhook-event', [StripeController::class, 'handleWebhookEvent'])->name('webhook-event');
