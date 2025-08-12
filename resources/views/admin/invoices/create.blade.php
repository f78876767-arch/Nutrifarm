@extends('admin.layout')

@section('title', 'Create Invoice')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Invoice</h1>
                <p class="text-gray-600 mt-1">Add a new payment invoice</p>
            </div>
            <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Invoices
            </a>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-xl p-8 border border-gray-100">
        <form method="POST" action="{{ route('admin.invoices.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <label for="external_id" class="block text-sm font-semibold text-gray-700 mb-2">External ID*</label>
                        <input type="text" id="external_id" name="external_id" value="{{ old('external_id') }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('external_id') border-red-500 @enderror"
                               placeholder="Enter unique external ID">
                        @error('external_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="user_id" class="block text-sm font-semibold text-gray-700 mb-2">User*</label>
                        <select id="user_id" name="user_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('user_id') border-red-500 @enderror">
                            <option value="">Select a user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="order_id" class="block text-sm font-semibold text-gray-700 mb-2">Order (Optional)</label>
                        <select id="order_id" name="order_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('order_id') border-red-500 @enderror">
                            <option value="">No linked order</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                                    Order #{{ $order->id }} - {{ $order->user->name }} (Rp {{ number_format($order->total_amount, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('order_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">Amount*</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="text" id="amount" name="amount" value="{{ old('amount') }}" required
                                   class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('amount') border-red-500 @enderror"
                                   placeholder="0" onkeyup="formatRupiah(this)">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div>
                        <label for="payer_email" class="block text-sm font-semibold text-gray-700 mb-2">Payer Email</label>
                        <input type="email" id="payer_email" name="payer_email" value="{{ old('payer_email') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('payer_email') border-red-500 @enderror"
                               placeholder="Enter payer email">
                        @error('payer_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-semibold text-gray-700 mb-2">Currency*</label>
                        <select id="currency" name="currency" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('currency') border-red-500 @enderror">
                            <option value="IDR" {{ old('currency', 'IDR') == 'IDR' ? 'selected' : '' }}>Indonesian Rupiah (IDR)</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="expiry_date" class="block text-sm font-semibold text-gray-700 mb-2">Expiry Date</label>
                        <input type="datetime-local" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('expiry_date') border-red-500 @enderror">
                        @error('expiry_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('description') border-red-500 @enderror"
                                  placeholder="Enter invoice description">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-8 border-t border-gray-200 mt-8">
                <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 active:from-primary-600 active:to-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                    <i class="fas fa-save mr-2"></i>
                    Create Invoice
                </button>
            </div>
        </form>
    </div>

    <script>
        // Rupiah formatting function
        function formatRupiah(input) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value).toLocaleString('id-ID');
                input.value = value;
            }
        }
        
        // Form submission - convert formatted prices to numbers
        document.querySelector('form').addEventListener('submit', function(e) {
            const amountInput = document.getElementById('amount');
            if (amountInput.value) {
                amountInput.value = amountInput.value.replace(/[^\d]/g, '');
            }
        });
    </script>
@endsection
