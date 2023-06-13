<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Account;
use Stripe\Token;
use Stripe\Payout;
use Stripe\Transfer;

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

    public function payout()
    {
        return view('payout');
    }

    public function createPayout(Request $request)
    {

        Stripe::setApiKey(env('STRIPE_SECRET'));
        // Tạo một tài khoản trong Stripe
        // $account = Account::create([
        //     'type' => 'custom',
        //     'country' => 'JP',
        //     'email' => 'hienluong1997@gmail.com',
        //     'capabilities' => [
        //         'card_payments' => ['requested' => true],
        //         'transfers' => ['requested' => true],
        //         'bank_transfer_payments' => ['requested' => true],
        //     ],
        // ]);

        // Tạo token cho thông tin ngân hàng hoặc thẻ tín dụng
        // $bankAccountToken = Token::create([
        //     'bank_account' => [
        //         'country' => 'JP',
        //         'currency' => 'jpy',
        //         'account_holder_name' => 'Test name',
        //         'account_holder_type' => 'individual',
        //         'routing_number' => '1100000',
        //         'account_number' => '0001234',
        //     ],
        // ]);

        // Tạo một tài khoản trong Stripe
        // $account = Account::create([
        //     'type' => 'custom',
        //     'country' => 'JP',
        //     'email' => 'hienluong1997@gmail.com',
        //     'capabilities' => [
        //         'card_payments' => ['requested' => true],
        //         'transfers' => ['requested' => true],
        //         'bank_transfer_payments' => ['requested' => true],
        //     ],
        //     'business_type' => 'individual',
        //     'individual' => [
        //         'first_name' => 'Test',
        //         'last_name' => 'Account',
        //         'email' => 'hienluong1997@gmail.com',
        //     ],
        //     'external_account' => $bankAccountToken->id,
        // ]);


        // $payout = Payout::create([
        //     'amount' => 100,
        //     'currency' => 'jpy',
        // ]);

        $transfer = Transfer::create([
            'amount' => 1000,
            'currency' => 'jpy',
            'destination' => 'acct_1NI4x3BNuopoBHPS',  // ID của tài khoản Stripe đích (người nhận)
            // ID của giao dịch nguồn (ví dụ: charge)
        ]);


        // Return the payment intent information to display to the user
        return response()->json([
            'transfer' => $transfer,
            // 'payout' => $payout
        ]);
    }
}
