<html>

<head>
    <title>Payment Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
</head>

<body>

    <h1 class="mt-4 mb-4">Google pay</h1>

    <div id="payment-request-button">
        <!-- A Stripe Element will be inserted here. -->
    </div>

    <div id="messages" role="alert"></div>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {

            // 1. Initialize Stripe
            const stripe = Stripe("{{ env('STRIPE_KEY') }}");

            // 2. Create a payment request object
            var paymentRequest = stripe.paymentRequest({
                country: 'JP',
                currency: 'jpy',
                total: {
                    label: 'Demo total',
                    amount: 10,
                },
                requestPayerName: true,
                requestPayerEmail: true,
            });

            // 3. Create a PaymentRequestButton element
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
                    '/payment/intent', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            currency: 'jpy',
                            paymentMethodType: 'card',
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
                    e.complete('fail');
                    return;
                }
                e.complete('success');

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

        });
    </script>
</body>

</html>