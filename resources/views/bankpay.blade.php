<head>
    <title>Payment Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<script src="https://js.stripe.com/v3/"></script>
<h1>Stripe Bank Transfer Payment</h1>

<form id="payment-form">
    <input type="text" id="cardholderName" name="cardholderName" placeholder="cardholderName" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="number" name="amount" placeholder="Amount" required><br>
    <button type="submit">Pay by bank transfer</button>
</form>

<script>
    // Initialize Stripe
    var stripe = Stripe("{{ env('STRIPE_KEY') }}");

    // Handle form submission
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        fetch("{{ route('payment.bank-transfer') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                name: form.cardholderName.value,
                email: form.email.value,
                amount: form.amount.value
            })
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            stripe.confirmPaymentIntent(data.client_secret).then(function(result) {
                if (result.error) {
                    console.log(result.error.message);
                } else {
                    const paymentIntent = result.paymentIntent;
                    if (paymentIntent.status === 'requires_action') {
                        const nextAction = paymentIntent.next_action;

                        if (nextAction.type === 'display_bank_transfer_instructions') {
                            // Redirect to hosted instructions URL for bank transfer
                            const hostedInstructionsUrl = nextAction.display_bank_transfer_instructions.hosted_instructions_url;
                            window.location.href = hostedInstructionsUrl;
                        } else if (nextAction.type === 'use_stripe_sdk') {
                            // Handle card action using Stripe.js
                            stripe.handleCardAction(paymentIntent.client_secret)
                                .then(function(result) {
                                    if (result.error) {
                                        console.log(result.error.message);
                                    } else {
                                        console.log(paymentIntent.id);
                                    }
                                });
                        } else {
                            console.log('Unsupported action type:', nextAction.type);
                        }
                    } else if (paymentIntent.status === 'succeeded') {
                        console.log('Payment has been successfully completed');
                    }
                }
            });
        });
    });
</script>