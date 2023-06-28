<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Refund;
use \Stripe\Exception\SignatureVerificationException;
use \Stripe\Webhook;

use Stripe\Account;
use Stripe\Token;
use Stripe\Payout;
use Stripe\Topup;
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
                'payment_intent' => 'pi_3NIoDbBn8Pm6BjZV334VILlX',
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



    public function handleWebhookEvent(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = 'whsec_T0B40FdLz03pj5lhbvfRzhhFPHpKLmui';

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            // Validation failed, handle error or finish processing
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Handle webhook notifications
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                // Then define and call a method to handle the successful

                Log::debug('Webhook received event', [$event->type]);
                Log::debug('Webhook event status',  ['succeeded']);
                // return view('webhook-event', ['paymentIntent' => $paymentIntent, 'even_status' => $even_status]);
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                // Then define and call a method to handle 
                Log::debug('Webhook received event', [$event->type]);
                Log::debug('Webhook event status',  ['payment failed']);
                break;
            case 'refund.created':
                $refund = $event->data->object;
                // Then define and call a method to handle 
                Log::debug('Webhook received event', [$event->type]);
                Log::debug('Webhook event status', ['refund created']);
                break;


            case 'refund.updated':
                $refund = $event->data->object;
                // Then define and call a method to handle 
                Log::debug('Webhook received event', [$event->type]);
                Log::debug('Webhook event status',  ['refund updated']);
                break;
            case 'customer.created':
                $customer = $event->data->object;
                // Then define and call a method to handle 
                Log::debug('Webhook received event', [$event->type]);
                Log::debug('Webhook event status',  ['customer created']);
                break;
                // Handle different events similarly
            default:
                // Unexpected event type
                error_log('Received unknown event type');
        }

        // Trả về HTTP response với mã status 200
        return response()->json(['success' => true], 200);
    }

    public function payout()
    {
        return view('payout');
    }

    public function getExternalAccounts(Request $request)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $externalAccounts = Account::allExternalAccounts(
                'acct_1NNWEiB4CTSrzQns', // ID of connected account
                [
                    'object' => 'bank_account',
                    // 'limit' => '',
                ]
            );

            return view('list-bank')->with('account_list', $externalAccounts);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get list external accounts . Error: ' . $e->getMessage(),
            ]);
        }
    }


    public function createBank()
    {
        return view('create-bank');
    }
    public function storeExternalAccount(Request $request)
    {

        $account_holder_name = $request->input('account_holder_name');
        $account_number = $request->input('account_number');
        $routing_number = $request->input('routing_number');

        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $external_account = Account::createExternalAccount(
                'acct_1NNWEiB4CTSrzQns', // ID của connected account
                [
                    'external_account' => [
                        'object' => 'bank_account',
                        'country' => 'JP',
                        'currency' => 'jpy',
                        'account_number' =>  $account_number,
                        'routing_number' => $routing_number,
                        'account_holder_name' =>  $account_holder_name,
                        'account_holder_type' => 'individual',
                    ],
                ]
            );
            return view('create-bank')->with('external_account', $external_account);
        } catch (\Exception $e) {
            return view('create-bank')->with('error', 'Failed to create external accounts . Error: ' . $e->getMessage());
        }
    }

    public function payoutResult()
    {
        return view('payout-result');
    }

    public function createPayoutOld(Request $request)
    {
        $destination = $request->input('bank_id');
        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $payout = Payout::create([
                'amount' => 131,
                'currency' => 'jpy', // Đơn vị tiền tệ
                'destination' => $destination, // ID tài khoản ngân hàng đích
            ], ['stripe_account' => 'acct_1NNWEiB4CTSrzQns']);
            return view('payout-result')->with('payout', $payout);
        } catch (\Exception $e) {
            return view('create-bank')->with('error', 'Failed to create payout . Error: ' . $e->getMessage());
        }
    }


    public function createPayout(Request $request)
    {
        $destination = $request->input('bank_id');
        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $payout = Payout::create([
                'amount' => 131,
                'currency' => 'jpy',
                'destination' => $destination, // ID of bank
                'description' => 'STRIPE PAYOUT for driver'
            ], ['stripe_account' => 'acct_1NNWEiB4CTSrzQns']);

            return view('payout-result')->with('payout', $payout);
        } catch (\Exception $e) {
            return view('payout-result')->with('error', 'Failed to create payout. Error: ' . $e->getMessage());
        }
    }


    public function createTopup(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $topup = Topup::create([
            'amount' => 2000,
            'currency' => 'jpy',
            'description' => 'Top-up for Jenny Rosen',
            'statement_descriptor' => 'Top-up',
        ]);

        // Return the payment intent information to display to the user
        return response()->json([
            'topup' => $topup,
        ]);
    }


    public function getListPayout(Request $request)
    {
        $destination = $request->input('bank_id');
        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $payout_list = Payout::all([
                'limit' => 100,
            ], ['stripe_account' => 'acct_1NNWEiB4CTSrzQns']);

            return view('list-payout')->with('payout_list', $payout_list);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payout list . Error: ' . $e->getMessage(),
            ]);
        }
    }
}
