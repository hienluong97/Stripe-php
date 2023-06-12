<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Refund;


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

    public function bankpay()
    {
        return view('bankpay');
    }

    public function createBankPaymentIntent(Request $request)
    {
        $email = $request->email;
        $name = $request->name;
        Stripe::setApiKey(env('STRIPE_SECRET'));
        // Handle the case when the customer already exists
        $existingCustomer = Customer::all(['email' => $email])->data;
        $customer = '';
        if (!$existingCustomer) {
            $customer = Customer::create([
                'email' => $email,
                'name' => $name
            ]);
        } else {
            $customer = $existingCustomer[0];
        }

        // Create a payment intent
        $intent = PaymentIntent::create([
            'amount' => $request->amount,
            'currency' => 'jpy',
            'customer' => $customer->id,
            'payment_method_types' => ['customer_balance'],
            'payment_method_data' => [
                'type' => 'customer_balance',
            ],
            'payment_method_options' => [
                'customer_balance' => [
                    'funding_type' => 'bank_transfer',
                    'bank_transfer' => [
                        'type' => 'jp_bank_transfer',
                    ],
                ],
            ],
        ]);

        // Return the payment intent information to display to the user
        return response()->json([
            'client_secret' => $intent->client_secret,
        ]);
    }

    public function bankRefund()
    {
        return view('bank-refund');
    }

    public function bankRefundBalance(Request $request)
    {
        $customerID = 'cus_O2L5wMhDKy2bhr';
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // get customer’s cash balance
            $customerBalance = Customer::retrieveCashBalance(
                $customerID,
                [
                    'limit' => null,
                    'starting_after' => null,
                    'ending_before' => null,
                ]
            );
            if ($customerBalance) {
                $availableBalance = $customerBalance->available->jpy;

                if ($availableBalance > 0) {
                    // create refund
                    $refund = Refund::create([
                        'amount' => $availableBalance,
                        'currency' => 'jpy',
                        'instructions_email' => 'hienluong1997@gmail.com',
                        'origin' => 'customer_balance',
                        'customer' => $customerID,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Refund successful!',
                        'refund' => $refund,
                        'customer_balance' => $availableBalance
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Refund failed. Error: your’s cash balance equal to 0.',
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve customer balance.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refund failed. Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function bankRefundPayment(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            // create refund
            $refund = Refund::create([
                'payment_intent' => 'pi_3NHp75Bn8Pm6BjZV3FhEgJva',
                // 'amount' => 666,
                'instructions_email' => 'hienluong1997@gmail.com',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund created successfully!',
                'refund' => $refund,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create refund. Error: ' . $e->getMessage(),
            ]);
        }
    }
}
