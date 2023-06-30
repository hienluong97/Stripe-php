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

Route::get('/list-bank', [StripeController::class, 'getExternalAccounts'])->name('list-bank');
Route::post('/store-bank', [StripeController::class, 'storeExternalAccount'])->name('store-bank');
Route::get('/create-bank', [StripeController::class, 'createBank'])->name('create-bank');
Route::post('/store-card', [StripeController::class, 'storeExternalAccountForCard'])->name('store-card');
Route::get('/create-card', [StripeController::class, 'createCard'])->name('create-card');

Route::post('/payout', [StripeController::class, 'createPayout'])->name('createPayout');
Route::get('/payout-result', [StripeController::class, 'createBankResult'])->name('payout-result');
Route::get('/list-payout', [StripeController::class, 'getListPayout'])->name('list-payout');
Route::post('/bank-transfer', [StripeController::class, 'bankTransfer'])->name('bank-transfer');


Route::get('/create-topup', [StripeController::class, 'createTopup'])->name('create-topup');
Route::post('/store-topup', [StripeController::class, 'storeTopup'])->name('store-topup');

Route::get('/create-transfer', [StripeController::class, 'createTransfer'])->name('create-transfer');
Route::post('/store-transfer', [StripeController::class, 'storeTransfer'])->name('store-transfer');
