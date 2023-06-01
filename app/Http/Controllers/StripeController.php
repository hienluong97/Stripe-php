<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeController extends Controller
{
    public function creditpay()
    {
        return view('credit-pay');
    }

    public function googlepay()
    {
        return view('googlepay');
    }
    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount,
            'currency' => 'jpy',
        ]);
        return response()->json(['clientSecret' => $paymentIntent->client_secret]);
    }
}
