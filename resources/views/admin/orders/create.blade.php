@extends('admin.layout')

@section('title', 'Create Order')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Create Order</h1>
            <a href="{{ route('admin.orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Orders
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form method="POST" action="{{ route('admin.orders.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">Customer</label>
                    <select id="user_id" name="user_id" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('user_id') border-red-500 @enderror">
                        <option value="">Select Customer (or leave blank for guest)</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="customer_email" class="block text-gray-700 text-sm font-bold mb-2">Customer Email (for guest orders)</label>
                    <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email') }}"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('customer_email') border-red-500 @enderror">
                    @error('customer_email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                <div>
                    <label for="subtotal_amount" class="block text-gray-700 text-sm font-bold mb-2">Subtotal Amount*</label>
                    <input type="number" step="0.01" id="subtotal_amount" name="subtotal_amount" value="{{ old('subtotal_amount') }}" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('subtotal_amount') border-red-500 @enderror">
                    @error('subtotal_amount')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tax_amount" class="block text-gray-700 text-sm font-bold mb-2">Tax Amount</label>
                    <input type="number" step="0.01" id="tax_amount" name="tax_amount" value="{{ old('tax_amount', 0) }}"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('tax_amount') border-red-500 @enderror">
                    @error('tax_amount')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="shipping_amount" class="block text-gray-700 text-sm font-bold mb-2">Shipping Amount</label>
                    <input type="number" step="0.01" id="shipping_amount" name="shipping_amount" value="{{ old('shipping_amount', 0) }}"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('shipping_amount') border-red-500 @enderror">
                    @error('shipping_amount')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label for="discount_amount" class="block text-gray-700 text-sm font-bold mb-2">Discount Amount</label>
                    <input type="number" step="0.01" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', 0) }}"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('discount_amount') border-red-500 @enderror">
                    @error('discount_amount')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="total_amount" class="block text-gray-700 text-sm font-bold mb-2">Total Amount*</label>
                    <input type="number" step="0.01" id="total_amount" name="total_amount" value="{{ old('total_amount') }}" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('total_amount') border-red-500 @enderror">
                    @error('total_amount')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <select id="status" name="status" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('status') border-red-500 @enderror">
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ old('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ old('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_status" class="block text-gray-700 text-sm font-bold mb-2">Payment Status</label>
                    <select id="payment_status" name="payment_status" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('payment_status') border-red-500 @enderror">
                        <option value="pending" {{ old('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ old('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ old('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ old('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                    @error('payment_status')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Create Order
                </button>
                <a href="{{ route('admin.orders.index') }}" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // Auto-calculate total when amounts change
        document.addEventListener('DOMContentLoaded', function() {
            const subtotal = document.getElementById('subtotal_amount');
            const tax = document.getElementById('tax_amount');
            const shipping = document.getElementById('shipping_amount');
            const discount = document.getElementById('discount_amount');
            const total = document.getElementById('total_amount');

            function calculateTotal() {
                const subtotalVal = parseFloat(subtotal.value) || 0;
                const taxVal = parseFloat(tax.value) || 0;
                const shippingVal = parseFloat(shipping.value) || 0;
                const discountVal = parseFloat(discount.value) || 0;
                
                const totalVal = subtotalVal + taxVal + shippingVal - discountVal;
                total.value = totalVal.toFixed(2);
            }

            [subtotal, tax, shipping, discount].forEach(input => {
                input.addEventListener('input', calculateTotal);
            });
        });
    </script>
@endsection
