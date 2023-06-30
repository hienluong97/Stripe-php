<head>
    <title>Create transfer </title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>

<h1>Create transfer</h1>

<body class="m-4">
    <div class="container mt-4">
        <form id="card-form" action="{{ route('store-transfer') }}" method="POST">
            @csrf
            <div id="cardErrors" role="alert" class="text-danger"></div>
            <button id="submit" type="submit" class="btn btn-primary mt-3">Transfer</button>
        </form>
        <div id="message" role="alert" class="text-danger"></div>
    </div>

    @if(isset($transfer))
    <h5 class="alert alert-success"> Transfer successfully with id :{{$transfer->id}}</h5>
    @endif

    @if(isset($error))
    <h5 class="alert alert-danger"> {{$error}}</h5>
    @endif
</body>