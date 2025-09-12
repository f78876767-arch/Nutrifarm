@extends('admin.layout')

@section('content')
    <h1>Favorites</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Product</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($favorites as $fav)
            <tr>
                <td>{{ $fav->id }}</td>
                <td>{{ optional($fav->user)->email }}</td>
                <td>{{ optional($fav->product)->name }}</td>
                <td>{{ $fav->created_at }}</td>
                <td>
                    <a href="{{ route('admin.favorites.show', $fav) }}">View</a>
                    <form method="POST" action="{{ route('admin.favorites.destroy', $fav) }}" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $favorites->links() }}
@endsection
