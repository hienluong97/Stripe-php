<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
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

    public function bankpay()
    {
        return view('bankpay');
    }

    public function createBankPaymentIntent(Request $request)
    {
        // Thiết lập khóa Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Tạo customer mới
        $customer = Customer::create([
            'email' => $request->input('email'),
        ]);

        // Tạo payment intent
        $intent = PaymentIntent::create([
            'amount' => $request->input('amount'),
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

        // Trả về thông tin payment intent để hiển thị cho người dùng
        return response()->json([
            'client_secret' => $intent->client_secret,
        ]);
    }

    public function completeBankPayment(Request $request)
    {
        // Khởi tạo Stripe với khóa bí mật của bạn
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Lấy PaymentIntent ID từ yêu cầu
        $paymentIntentId = $request->input('paymentIntentId');

        try {
            // Hoàn thành thanh toán bằng cách xác nhận PaymentIntent
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->confirm();

            // Kiểm tra trạng thái của PaymentIntent
            if ($paymentIntent->status === 'succeeded') {
                // Thanh toán đã hoàn thành
                return response()->json(['status' => 'success']);
            } else {
                // Thanh toán chưa hoàn thành
                return response()->json(['status' => 'incomplete']);
            }
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
