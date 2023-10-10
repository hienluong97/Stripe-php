<head>
    <title>invoice</title>

    {{-- to allow mixed content --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
</head>


<form  action="{{ route('create-invoice') }}" method="POST">
    @csrf
    <button type="submit" >create Invoice</button>
</form>

<body class="m-4">
    <h1>create Invoice-result</h1>
    @if(isset($invoice_item))
    <h5 class="alert alert-success"> Create Invoice successfull with id :{{$invoice_item->id}}</h5>
    @endif

    @if(isset($error))
    <h5 class="alert alert-danger"> {{$error}}</h5>
    @endif

<form id="send-invoice" action="{{ route('send-invoice') }}" method="POST">
    @csrf
    <input type="hidden" name="invoice_id" value="{{$invoice_item->invoice ?? " "}}">
    <button type="submit" >send Invoice</button>
</form>


    <h1>send Invoice-result</h1>
    @if(isset($send_invoice))
    <h5 class="alert alert-success"> send Invoice successfull with id :{{$send_invoice->id}}</h5>

     <a target="_blank" href= {{$send_invoice->invoice_pdf}} > See preview </a>
     <a target="_blank" href= {{$send_invoice->hosted_invoice_url}} > pay now </a>
    @endif

    @if(isset($error))
    <h5 class="alert alert-danger"> {{$error}}</h5>
    @endif
</body>