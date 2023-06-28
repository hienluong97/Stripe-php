<head>
    <title>List Bank</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>


<body class="mt-4 ml-4">
    @foreach ($payout_list as $payout)

    <div>
        <p>{{ $payout->id}}</p> <span>{{ $payout->description}}</span>
        <span class="btn-primary mt-3">{{ $payout->status}}</span>
        <span class="btn-primary  alert-danger mt-3">{{ $payout->failure_message}}</span>
    </div>
    @endforeach
</body>