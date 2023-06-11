<head>
    <title>Payment Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<script src="https://js.stripe.com/v3/"></script>


<section class='ml-4'>
    <h5 class='mt-4 mb-4'>Refund a customer’s cash balance using the API</h5>
    <form method="POST" id="refund-form">
        @csrf
        <button class="btn btn-primary" type="submit">Refund</button>
    </form>
    <p id="messageEl" class="text-danger"></p>
</section>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Stripe
        var stripe = Stripe("{{ env('STRIPE_KEY') }}");
        var form = document.getElementById('refund-form');
        var errorElement = document.getElementById('messageEl');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Create Payment Intent 
            const response = await fetch("{{ route('bank-refund') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                })
            });

            const data = await response.json();
            console.log(data)
            const {
                success,
                message,
                refund
            } = data;

            console.log(success)
            console.log(message)
            console.log(refund)
            messageEl.innerText = message;
        });
    })
</script>