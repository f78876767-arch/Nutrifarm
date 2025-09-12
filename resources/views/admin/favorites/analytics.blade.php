@extends('admin.layout')

@section('content')
    <h1>Favorites Analytics</h1>
    <ul>
        @foreach($top as $row)
            <li>Product ID {{ $row->product_id }}: {{ $row->cnt }} favorites</li>
        @endforeach
    </ul>
@endsection
