<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeController extends Controller
{
    public function checkout()
    {
        // Stripe::setApiKey(env('STRIPE_SECRET'));

        // $paymentIntent = PaymentIntent::create([
        //     'amount' => 1000, // Số tiền thanh toán (đơn vị cents)
        //     'currency' => 'jpy',
        // ]);

        // return view('checkout', ['clientSecret' => $paymentIntent->client_secret]);
        return view('checkout');
    }

    public function payment(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount,
            'currency' => 'jpy',
        ]);

        return response()->json(['clientSecret' => $paymentIntent->client_secret]);
    }
}
