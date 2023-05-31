<!DOCTYPE html>
<html>

<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>

<body>
    <div class="container mt-4">
        <h1>Checkout</h1>
        <form id="paymentForm" action="{{ route('payment') }}" method="post">
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
            <div id="cardErrors" role="alert"></div>
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
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        name: document.getElementById('cardholderName').value
                    }
                }).then(function(result) {
                    if (result.error) {
                        errorElement.textContent = result.error.message;
                    } else {
                        stripe.confirmCardPayment("{{ $clientSecret }}", {
                            payment_method: result.paymentMethod.id
                        }).then(function(result) {
                            if (result.error) {
                                errorElement.textContent = result.error.message;
                            } else {
                                console.log(result.paymentIntent);
                                // Handle successful payment
                                form.submit();
                            }
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>