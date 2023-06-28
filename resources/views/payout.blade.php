<head>
    <title>Payment Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<script src="https://js.stripe.com/v3/"></script>
<h1>Create bank</h1>

<body class="m-4">

    <span> {{$id}}</span>
    <form action="{{ route('store-bank') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="account_holder_name">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Payout</button>
        <a href="{{ route('list-bank') }}" class="btn btn-primary ">Show list bank</a>
    </form>
</body>