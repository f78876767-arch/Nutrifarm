@extends('admin.layout')

@section('title', 'User Details')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">User: {{ $user->name }}</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Details -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">User Information</h2>
                
                <div class="flex items-start space-x-4 mb-6">
                    @if($user->profile_photo_path)
                        <img class="h-20 w-20 rounded-full object-cover" src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}">
                    @else
                        <div class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-xl font-medium text-gray-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($user->role ?? 'customer') }}
                            </span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($user->phone)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->phone }}</p>
                    </div>
                    @endif

                    @if($user->date_of_birth)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->date_of_birth->format('M d, Y') }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Verified</label>
                        @if($user->email_verified_at)
                            <p class="mt-1 text-sm text-green-600">Verified on {{ $user->email_verified_at->format('M d, Y g:i A') }}</p>
                        @else
                            <p class="mt-1 text-sm text-red-600">Not verified</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Member Since</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                @if($user->address)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->address }}</p>
                </div>
                @endif
            </div>

            <!-- User Orders -->
            @if($user->orders && $user->orders->count() > 0)
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Orders ({{ $user->orders->count() }})</h2>
                
                <div class="space-y-4">
                    @foreach($user->orders->take(5) as $order)
                        <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Order #{{ $order->order_number }}</h3>
                                    <p class="text-sm text-gray-600">{{ $order->items->count() }} items</p>
                                    <div class="mt-2 flex items-center space-x-4">
                                        <span class="text-sm font-medium text-green-600">${{ number_format($order->total_amount, 2) }}</span>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            @if($order->status === 'completed') bg-green-100 text-green-800
                                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                        <span class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                                    View Order
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Orders</h2>
                <p class="text-gray-500">This user hasn't placed any orders yet.</p>
            </div>
            @endif
        </div>

        <!-- User Stats -->
        <div class="space-y-6">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Statistics</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Orders</label>
                        <p class="text-2xl font-bold text-green-600">{{ $user->orders ? $user->orders->count() : 0 }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Spent</label>
                        <p class="text-2xl font-bold text-blue-600">${{ number_format($user->orders ? $user->orders->sum('total_amount') : 0, 2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Created</label>
                        <p class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y g:i A') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <p class="text-sm text-gray-900">{{ $user->updated_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                
                <div class="space-y-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                        Edit User
                    </a>

                    @if(!$user->email_verified_at)
                    <button class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" onclick="alert('Email verification functionality would be implemented here.')">
                        Send Verification Email
                    </button>
                    @endif
                    
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            Delete User
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
