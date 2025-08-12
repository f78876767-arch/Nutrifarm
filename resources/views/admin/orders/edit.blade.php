@extends('admin.layout')

@section('title', 'Edit Order')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Edit Order #{{ $order->order_number }}</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.orders.show', $order) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    View Order
                </a>
                <a href="{{ route('admin.orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Orders
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form method="POST" action="{{ route('admin.orders.update', $order) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Number</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $order->order_number }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">
                        {{ $order->user ? $order->user->name : 'Guest Customer' }}
                        @if($order->customer_email)
                            <br>{{ $order->customer_email }}
                        @elseif($order->user)
                            <br>{{ $order->user->email }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Order Status*</label>
                    <select id="status" name="status" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('status') border-red-500 @enderror">
                        <option value="pending" {{ old('status', $order->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ old('status', $order->status) === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ old('status', $order->status) === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="completed" {{ old('status', $order->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $order->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_status" class="block text-gray-700 text-sm font-bold mb-2">Payment Status</label>
                    <select id="payment_status" name="payment_status" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('payment_status') border-red-500 @enderror">
                        <option value="pending" {{ old('payment_status', $order->payment_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ old('payment_status', $order->payment_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ old('payment_status', $order->payment_status) === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ old('payment_status', $order->payment_status) === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                    @error('payment_status')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Order Summary (Read-only) -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Order Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500">Subtotal</label>
                        <p class="text-sm font-medium">${{ number_format($order->subtotal_amount, 2) }}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Tax</label>
                        <p class="text-sm font-medium">${{ number_format($order->tax_amount, 2) }}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Shipping</label>
                        <p class="text-sm font-medium">${{ number_format($order->shipping_amount, 2) }}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Total</label>
                        <p class="text-lg font-bold text-gray-900">${{ number_format($order->total_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Order Notes</label>
                <textarea id="notes" name="notes" rows="3"
                          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('notes') border-red-500 @enderror"
                          placeholder="Add notes about this order...">{{ old('notes', $order->notes) }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update Order
                </button>
                <a href="{{ route('admin.orders.index') }}" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Status Change Log (if you want to track changes) -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Order Status Guide:</strong>
                </p>
                <ul class="mt-2 text-sm text-blue-600 list-disc list-inside">
                    <li><strong>Pending:</strong> Order received, awaiting processing</li>
                    <li><strong>Processing:</strong> Order is being prepared</li>
                    <li><strong>Shipped:</strong> Order has been sent to customer</li>
                    <li><strong>Completed:</strong> Order delivered and completed</li>
                    <li><strong>Cancelled:</strong> Order has been cancelled</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
