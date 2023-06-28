<head>
    <title>List Bank</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>


<body class="mt-4 ml-4">
    @foreach ($account_list as $item)
    <form id="payout" action="{{ route('createPayout') }}" method="POST">
        @csrf
        <input type="hidden" name="bank_id" value="{{ $item->id}}">
        <span>{{ $item->account_holder_name}} - bank number last : {{ $item->last4}}</span>
        <button type="submit" class="btn-primary mt-3">payout</button>
    </form>
    @endforeach

    <a href="{{ route('create-bank') }}" class="btn btn-primary mt-3">Add bank</a>
</body>