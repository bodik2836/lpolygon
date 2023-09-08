@extends('layouts.default')

@section('content')
    <div class="container">
        @if($transInfo['status'] == 'success')
            <h1 class="text-success">{{ $transInfo['msg'] }}</h1>

            <h4>Payment Information</h4>
            <p><b>Reference Number:</b> {{ $transInfo['ref_id'] }}</p>
            <p><b>Order ID:</b> {{ $transaction->order_id }}</p>
            <p><b>Transaction ID:</b> {{ $transaction->transaction_id }}</p>
            <p><b>Paid Amount:</b> {{ $transaction->paid_amount . ' ' . $transaction->paid_amount_currency }}</p>
            <p><b>Payment Status:</b> {{ $transaction->payment_status }}</p>
            <p><b>Date:</b> {{ $transaction->created_at }}</p>

            <h4>Payer Information</h4>
            <p><b>ID:</b> {{ $transaction->payer_id }}</p>
            <p><b>Name:</b>{{ $transaction->payer_name }}</p>
            <p><b>Email:</b> {{ $transaction->payer_email }}</p>
            <p><b>Country:</b> {{ $transaction->payer_country }}</p>

            <h4>Product Information</h4>
            <p><b>Name:</b> {{ $transaction->item_name }}</p>
            <p><b>Price:</b> {{ $transaction->item_price . ' ' . $transaction->item_price_currency }}</p>
        @else
            <h1 class="text-danger">Your Payment been failed!</h1>
            <p>{{ $transInfo['msg'] }}</p>
        @endif
    </div>
@endsection
