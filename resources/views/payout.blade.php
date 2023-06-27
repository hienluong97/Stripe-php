<head>
    <title>Payment Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<script src="https://js.stripe.com/v3/"></script>
<h1>Stripe payout</h1>

<form id="payout">
    <button type="submit" class="btn btn-primary mt-3">Test payout</button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Stripe
        var stripe = Stripe("{{ env('STRIPE_KEY') }}");
        var form = document.getElementById('payout');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Create Payment Intent 
            const response = await fetch("{{ route('createPayout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            });

            const data = await response.json();
            console.log(data)

        });

    })
</script>