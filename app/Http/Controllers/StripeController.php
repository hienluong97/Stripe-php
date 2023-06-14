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
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = 'whsec_DcFKEFFrKkwx3Dcc7QOR4lzf7J2hcrQL';

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
}
