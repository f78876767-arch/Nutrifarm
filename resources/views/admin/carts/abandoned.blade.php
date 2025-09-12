@extends('admin.layout')

@section('content')
    <h1>Abandoned Carts</h1>
    <ul>
        @foreach($items as $it)
            <li>{{ optional($it->user)->email }} - {{ optional($it->product)->name }} x {{ $it->quantity }} ({{ $it->updated_at }})</li>
        @endforeach
    </ul>
    {{ $items->links() }}
@endsection
