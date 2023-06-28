<head>
    <title>Payment Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<script src="https://js.stripe.com/v3/"></script>
<h1>payout-result</h1>

<body class="m-4">

    @if(isset($payout))
    <h5 class="alert alert-success"> Create payout successfull with id :{{$payout->id}}</h5>
    @endif

    @if(isset($error))
    <h5 class="alert alert-danger"> {{$error}}</h5>
    @endif
    <a href="{{ route('list-bank') }}" class="btn btn-primary ">Back to list bank</a>
</body>