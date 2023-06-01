# flutter_local_notifications

### 利用パッケージ

stripe-php

## Install and configure the stripe-php library:

```
composer require stripe/stripe-php

```

インストール コマンド

```
flutter pub get

```

## Create Stripe test account.

1. Register here https://dashboard.stripe.com/register.
1. Get Secret Key and(Publishable Key.

## Provide your Stripe API keys in the .env file of your Laravel project.

#### .env

```
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
```

## Create a route in your routes/web.php file to handle the payment process, for

#### routes/web.php

```

Route::get('/googlepay', [StripeController::class, 'googlepay'])->name('googlepay');
Route::post('/payment/intent', [StripeController::class, 'createPaymentIntent'])->name('payment.intent');

```

#### Set up the necessary routes and controllers:

```
php artisan make:controller StripeController

```

#### app/Http/Controllers/StripeController.php

```
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
```

#### Create the view:

Use Payment Request Button

About Prerequisites : read hear
https://stripe.com/docs/stripe-js/elements/payment-request-button?client=html#html-js-prerequisites

#### set up Stripe Element:

##### resources/views/googlepay.blade.php:

```
 <h1 class="mt-4 mb-4">Google pay</h1>
    <div id="payment-request-button">
        <!-- A Stripe Element will be inserted here. -->
    </div>
    <div id="messages" role="alert"></div>
```

#### add support function:

```
<script>
    // Helper for displaying status messages.
    const addMessage = (message) => {
        const messagesDiv = document.querySelector('#messages');
        messagesDiv.style.display = 'block';
        const messageWithLinks = addDashboardLinks(message);
        messagesDiv.innerHTML += `> ${messageWithLinks}<br>`;
        console.log(`Debug: ${message}`);
    };
    // Adds links for known Stripe objects to the Stripe dashboard.
    const addDashboardLinks = (message) => {
        const piDashboardBase = 'https://dashboard.stripe.com/test/payments';
        return message.replace(
            /(pi_(\S*)\b)/g,
            `<a href="${piDashboardBase}/$1" target="_blank">$1</a>`
        );
    };
</script>

<script src="https://js.stripe.com/v3/"></script>

```

### Use JavaScript to handle the form submission and Google Pay integration.

#### resources/views/googlepay.blade.php

1. Initialize Stripe

```
 const stripe = Stripe("{{ env('STRIPE_KEY') }}");
```

2. Create a payment request object

```
 var paymentRequest = stripe.paymentRequest({
        country: 'JP',
        currency: 'jpy',
        total: {
            label: 'Demo total',
            amount: paymentAmount,
        },
        requestPayerName: true,
        requestPayerEmail: true,
    });
```

3. Create a PaymentRequestButton element

```

 const elements = stripe.elements();
    const prButton = elements.create('paymentRequestButton', {
        paymentRequest: paymentRequest,
    });

    // Check the availability of the Payment Request API,
    // then mount the PaymentRequestButton
    paymentRequest.canMakePayment().then(function(result) {
        if (result) {
            prButton.mount('#payment-request-button');
        } else {
            document.getElementById('payment-request-button').style.display = 'none';
            addMessage('Google Pay support not found. Check the pre-requisites above and ensure you are testing in a supported browser.');
        }
    });

    paymentRequest.on('paymentmethod', async (e) => {
        // Make a call to the server to create a new
        // payment intent and store its client_secret.
        const {
            error: backendError,
            clientSecret
        } = await fetch(
            "{{ route('payment.intent') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    currency: 'jpy',
                    paymentMethodType: 'card',
                    amount: paymentAmount
                }),
            }
        ).then((r) => r.json());

        if (backendError) {
            addMessage(backendError.message);
            e.complete('fail');
            return;
        }

        addMessage(`Client secret returned.`);

        // Confirm the PaymentIntent without handling potential next actions (yet).
        let {
            error,
            paymentIntent
        } = await stripe.confirmCardPayment(
            clientSecret, {
                payment_method: e.paymentMethod.id,
            }, {
                handleActions: false,
            }
        );

        if (error) {
            addMessage(error.message);

            // Report to the browser that the payment failed, prompting it to
            // re-show the payment interface, or show an error message and close
            // the payment interface.
            e.complete('fail');
            return;
        }
        // Report to the browser that the confirmation was successful, prompting
        // it to close the browser payment method collection interface.
        e.complete('success');

        // Check if the PaymentIntent requires any actions and if so let Stripe.js
        // handle the flow. If using an API version older than "2019-02-11" instead
        // instead check for: `paymentIntent.status === "requires_source_action"`.
        if (paymentIntent.status === 'requires_action') {
            // Let Stripe.js handle the rest of the payment flow.
            let {
                error,
                paymentIntent
            } = await stripe.confirmCardPayment(
                clientSecret
            );
            if (error) {
                // The payment failed -- ask your customer for a new payment method.
                addMessage(error.message);
                return;
            }
            addMessage(`Payment ${paymentIntent.status}: ${paymentIntent.id}`);
        }

        addMessage(`Payment ${paymentIntent.status}: ${paymentIntent.id}`);
    });
```
