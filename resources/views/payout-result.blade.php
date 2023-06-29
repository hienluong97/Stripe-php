<head>
    <title>payout-result</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<body class="m-4">
    <h1>payout-result</h1>
    @if(isset($payout))
    <h5 class="alert alert-success"> Create payout successfull with id :{{$payout->id}}</h5>
    @endif

    @if(isset($error))
    <h5 class="alert alert-danger"> {{$error}}</h5>
    @endif
    <a href="{{ route('list-bank') }}" class="btn btn-primary ">Back to list bank</a>
    <a href="{{ route('list-payout') }}" class="btn btn-primary ">Back to list payout</a>
</body>