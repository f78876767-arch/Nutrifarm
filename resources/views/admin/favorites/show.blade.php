@extends('admin.layout')

@section('content')
    <h1>Favorite #{{ $favorite->id }}</h1>
    <p>User: {{ optional($favorite->user)->email }}</p>
    <p>Product: {{ optional($favorite->product)->name }}</p>
    <p>Created: {{ $favorite->created_at }}</p>
@endsection
