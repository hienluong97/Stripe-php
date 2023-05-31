

<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>


    <div class="container mt-4">
        <h1>Checkout</h1>
        <form id="paymentForm">
            @csrf
            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="cardholderName">Cardholder Name:</label>
                <input type="text" id="cardholderName" name="cardholderName" class="form-control" required>
            </div>
            <div id="cardElement" class="form-control"></div>
            <div id="cardErrors" role="alert" class="text-danger"></div>
            <button id="checkoutButton" type="submit" class="btn btn-primary mt-3">Checkout</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var stripe = Stripe("{{ env('STRIPE_KEY') }}");
            var elements = stripe.elements();
            var cardElement = elements.create('card');
            cardElement.mount('#cardElement');

            var form = document.getElementById('paymentForm');
            var errorElement = document.getElementById('cardErrors');


            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                // Create Payment Intent
                const response = await fetch("{{ route('payment') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        amount: document.getElementById('amount').value,
                    }),
                });

                const data = await response.json();
                const {
                    clientSecret
                } = data;

                // Confirm Card Payment
                const {
                    paymentIntent,
                    error
                } = await stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: cardElement,
                    }
                });

                if (error) {
                    // Handle payment error
                    console.error(error.message);
                    errorElement.textContent = error.message;
                } else {
                    // Payment success
                    console.log(paymentIntent);
                    // Redirect or show success message
                }
            });


        });
    </script>



