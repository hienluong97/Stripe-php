<head>
    <title>Payment Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<script src="https://js.stripe.com/v3/"></script>

<h1>Stripe Bank Transfer Payment</h1>

<form id="payment-form">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="number" name="amount" placeholder="Amount" required><br>
    <button type="submit">Pay by bank</button>
</form>

<script>
    var stripe = Stripe("{{ env('STRIPE_KEY') }}");
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
                            const hostedInstructionsUrl = nextAction.display_bank_transfer_instructions.hosted_instructions_url;
                            window.location.href = hostedInstructionsUrl;
                        } else if (nextAction.type === 'use_stripe_sdk') {
                            stripe.handleCardAction(paymentIntent.client_secret)
                                .then(function(result) {
                                    if (result.error) {
                                        console.log(result.error.message);
                                    } else {
                                        completePayment(paymentIntent.id);
                                    }
                                });
                        } else {
                            console.log('Loại hành động không được hỗ trợ:', nextAction.type);
                        }
                    } else if (paymentIntent.status === 'succeeded') {
                        console.log('Thanh toán đã hoàn thành');
                        completePayment(paymentIntent.id);
                    }
                }
            });
        });
    });

    function completePayment(paymentIntentId) {
        fetch("{{ route('payment.bank-transfer.complete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    paymentIntentId: paymentIntentId
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.status === 'success') {
                    console.log('Payment completed successfully');
                    // TODO: Xử lý khi thanh toán hoàn thành thành công
                } else {
                    console.log('Payment completion failed');
                    // TODO: Xử lý khi thanh toán không thành công
                }
            })
            .catch(function(error) {
                console.log('An error occurred during payment completion');
                console.log(error);
                // TODO: Xử lý lỗi nếu có
            });
    }
</script>