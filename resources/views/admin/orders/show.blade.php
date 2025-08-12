@extends('admin.layout')

@section('title', 'Order Details')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.orders.edit', $order) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Edit Status
                </a>
                <a href="{{ route('admin.orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Orders
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->order_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('M d, Y g:i A') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $order->user ? $order->user->name : 'Guest Customer' }}
                            @if($order->customer_email)
                                <br>{{ $order->customer_email }}
                            @elseif($order->user)
                                <br>{{ $order->user->email }}
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @if($order->status === 'completed') bg-green-100 text-green-800
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @if($order->payment_status === 'paid') bg-green-100 text-green-800
                                @elseif($order->payment_status === 'failed') bg-red-100 text-red-800
                                @elseif($order->payment_status === 'refunded') bg-purple-100 text-purple-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($order->notes)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $order->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Order Items -->
            @if($order->orderProducts && $order->orderProducts->count() > 0)
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Items</h2>
                
                <div class="space-y-4">
                    @foreach($order->orderProducts as $item)
                        <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center">
                                    @if($item->product && $item->product->image_path)
                                        <img class="h-16 w-16 rounded object-cover mr-4" src="{{ Storage::url($item->product->image_path) }}" alt="{{ $item->product->name }}">
                                    @endif
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">{{ $item->product ? $item->product->name : 'Product not found' }}</h3>
                                        <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                                        <p class="text-sm text-gray-600">Unit Price: ${{ number_format($item->price, 2) }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-medium text-gray-900">${{ number_format($item->price * $item->quantity, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Items</h2>
                <p class="text-gray-500">No items found for this order.</p>
            </div>
            @endif
        </div>

        <!-- Order Summary -->
        <div class="space-y-6">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Summary</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Subtotal</span>
                        <span class="text-sm text-gray-900">${{ number_format($order->subtotal_amount, 2) }}</span>
                    </div>

                    @if($order->tax_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Tax</span>
                        <span class="text-sm text-gray-900">${{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    @endif

                    @if($order->shipping_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Shipping</span>
                        <span class="text-sm text-gray-900">${{ number_format($order->shipping_amount, 2) }}</span>
                    </div>
                    @endif

                    @if($order->discount_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Discount</span>
                        <span class="text-sm text-red-600">-${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    @endif

                    <div class="border-t pt-3">
                        <div class="flex justify-between">
                            <span class="text-base font-medium text-gray-900">Total</span>
                            <span class="text-base font-bold text-gray-900">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                
                <div class="space-y-2">
                    <a href="{{ route('admin.orders.edit', $order) }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                        Update Status
                    </a>
                    
                    <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">
                            Delete Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
