@extends('admin.layout')

@section('content')
    <h1>Carts</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Updated</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($carts as $it)
            <tr>
                <td>{{ $it->id }}</td>
                <td>{{ optional($it->user)->email }}</td>
                <td>{{ optional($it->product)->name }}</td>
                <td>{{ $it->quantity }}</td>
                <td>{{ $it->updated_at }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.carts.remove-item', $it->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Remove</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $carts->links() }}
@endsection
