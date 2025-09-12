@extends('admin.layout')

@section('content')
    <h1>Cart for User #{{ $cart }}</h1>
    <ul>
        @foreach($items as $it)
            <li>{{ optional($it->product)->name }} x {{ $it->quantity }}</li>
        @endforeach
    </ul>
@endsection
