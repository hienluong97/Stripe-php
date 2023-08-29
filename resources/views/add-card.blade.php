<head>
    <title>Check card</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<h1>Check Card</h1>

<body class="m-4">

    <div class="container mt-4">
        <form id="card-form">
            @csrf
            <div class="form-group">
                <label for="cardholderName">Cardholder Name:</label>
                <input type="text" id="cardholderName" name="cardholderName" class="form-control" required>
            </div>
            <div id="cardElement" class="form-control"></div>
            <div id="cardErrors" role="alert" class="text-danger"></div>
            <button id="submit" type="submit" class="btn btn-primary mt-3">Add card</button>
        </form>
        <div id="message" role="alert" class="text-danger"></div>
    </div>


</body>

<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe("{{ env('STRIPE_KEY') }}");
    var elements = stripe.elements();
    var cardElement = elements.create('card');
    cardElement.mount('#cardElement');

    var form = document.getElementById('card-form');
    var errorElement = document.getElementById('message');


    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const {
            token,
            error
        } = await stripe.createToken(cardElement);

        if (error) {
            // Handle  error
            console.error(error.message);
            errorElement.textContent = error.message;
        } else {
            console.log(token);
            const response = await fetch("{{ route('check-card') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    token: token.id,
                }),
            });


            try {
                    const responseData = await response.json();
                    const { msg, message } = responseData;
                    console.log(msg)
                    errorElement.textContent = message;
                } catch (error) {
                    console.error('Error parsing JSON response:', error);
                    errorElement.textContent = 'An error occurred while processing your request.';
                }
        }
    });
</script>