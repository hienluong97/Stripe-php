<head>
    <title>Create bank</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<h1>Create bank</h1>

<body class="m-4">
    <form action="{{ route('store-bank') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="account_holder_name">Account Holder Name</label>
            <input type="text" name="account_holder_name" id="account_holder_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="account_number">Account Number</label>
            <input type="text" name="account_number" id="account_number" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="bank_name">Routing Number</label>
            <input type="text" name="routing_number" id="routing_number" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Bank Account</button>
        <a href="{{ route('list-bank') }}" class="btn btn-primary ">Show list bank</a>
    </form>

    @if(isset($external_account))
    <h5 class="alert alert-success"> Add bank account successfull with id :{{$external_account->id}}</h5>
    @endif

    @if(isset($error))
    <h5 class="alert alert-danger"> {{$error}}</h5>
    @endif
</body>