@extends('admin.layout')

@section('title', 'Edit Discount')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Discount: {{ $discount->name }}</h1>
                <p class="text-gray-600 mt-1">Update discount information and settings</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.discounts.show', $discount) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 transition ease-in-out duration-150">
                    <i class="fas fa-eye mr-2"></i>
                    View
                </a>
                <a href="{{ route('admin.discounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Discounts
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-xl p-8 border border-gray-100">
        <form method="POST" action="{{ route('admin.discounts.update', $discount) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Discount Name*</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $discount->name) }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('name') border-red-500 @enderror"
                               placeholder="Enter discount name">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('description') border-red-500 @enderror"
                                  placeholder="Enter discount description">{{ old('description', $discount->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">Discount Type*</label>
                            <select id="type" name="type" required onchange="toggleDiscountFields()"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('type') border-red-500 @enderror">
                                <option value="">Select Type</option>
                                <option value="percentage" {{ old('type', $discount->type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed_amount" {{ old('type', $discount->type) == 'fixed_amount' ? 'selected' : '' }}>Fixed Amount (Rp)</option>
                                <option value="buy_x_get_y" {{ old('type', $discount->type) == 'buy_x_get_y' ? 'selected' : '' }}>Buy X Get Y</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="value" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span id="valueLabel">Discount Value*</span>
                            </label>
                            <input type="text" id="value" name="value" value="{{ old('value', $discount->value) }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('value') border-red-500 @enderror"
                                   placeholder="Enter value">
                            @error('value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div id="buyXGetYField" style="display: {{ $discount->type == 'buy_x_get_y' ? 'block' : 'none' }};">
                        <label for="get_quantity" class="block text-sm font-semibold text-gray-700 mb-2">Get Quantity</label>
                        <input type="number" id="get_quantity" name="get_quantity" value="{{ old('get_quantity', $discount->get_quantity) }}" min="1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150"
                               placeholder="Number of free items">
                        @error('get_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="min_purchase_amount" class="block text-sm font-semibold text-gray-700 mb-2">Minimum Purchase (Rp)</label>
                            <input type="text" id="min_purchase_amount" name="min_purchase_amount" 
                                   value="{{ $discount->min_purchase_amount ? number_format($discount->min_purchase_amount, 0, ',', '.') : '' }}"
                                   onkeyup="formatRupiah(this)"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150"
                                   placeholder="Optional minimum purchase">
                            @error('min_purchase_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_discount_amount" class="block text-sm font-semibold text-gray-700 mb-2">Maximum Discount (Rp)</label>
                            <input type="text" id="max_discount_amount" name="max_discount_amount" 
                                   value="{{ $discount->max_discount_amount ? number_format($discount->max_discount_amount, 0, ',', '.') : '' }}"
                                   onkeyup="formatRupiah(this)"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150"
                                   placeholder="Optional maximum discount">
                            @error('max_discount_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="starts_at" class="block text-sm font-semibold text-gray-700 mb-2">Start Date</label>
                            <input type="datetime-local" id="starts_at" name="starts_at" 
                                   value="{{ $discount->starts_at ? $discount->starts_at->format('Y-m-d\TH:i') : '' }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150">
                            @error('starts_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ends_at" class="block text-sm font-semibold text-gray-700 mb-2">End Date</label>
                            <input type="datetime-local" id="ends_at" name="ends_at" 
                                   value="{{ $discount->ends_at ? $discount->ends_at->format('Y-m-d\TH:i') : '' }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150">
                            @error('ends_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="usage_limit" class="block text-sm font-semibold text-gray-700 mb-2">Usage Limit</label>
                        <input type="number" id="usage_limit" name="usage_limit" value="{{ old('usage_limit', $discount->usage_limit) }}" min="1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150"
                               placeholder="Optional usage limit">
                        @error('usage_limit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Apply to Products</label>
                        <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-4 space-y-2">
                            @forelse($products as $product)
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="products[]" value="{{ $product->id }}" 
                                           {{ in_array($product->id, old('products', $discount->products->pluck('id')->toArray())) ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <div class="flex items-center space-x-2">
                                        @if($product->image_path)
                                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-8 h-8 rounded object-cover">
                                        @endif
                                        <div>
                                            <span class="text-sm text-gray-700">{{ $product->name }}</span>
                                            <span class="text-xs text-gray-500 ml-2">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">No products available. <a href="{{ route('admin.products.create') }}" class="text-primary-600 hover:text-primary-700">Create one first</a>.</p>
                            @endforelse
                        </div>
                        @error('products')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $discount->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">Discount is active</label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-8 border-t border-gray-200 mt-8">
                <a href="{{ route('admin.discounts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 active:from-primary-600 active:to-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                    <i class="fas fa-save mr-2"></i>
                    Update Discount
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

        // Toggle discount fields based on type
        function toggleDiscountFields() {
            const type = document.getElementById('type').value;
            const valueLabel = document.getElementById('valueLabel');
            const valueInput = document.getElementById('value');
            const buyXGetYField = document.getElementById('buyXGetYField');

            if (type === 'percentage') {
                valueLabel.textContent = 'Percentage (%)';
                valueInput.placeholder = 'e.g., 20 for 20%';
                buyXGetYField.style.display = 'none';
            } else if (type === 'fixed_amount') {
                valueLabel.textContent = 'Fixed Amount (Rp)';
                valueInput.placeholder = 'e.g., 10000';
                valueInput.onkeyup = function() { formatRupiah(this); };
                buyXGetYField.style.display = 'none';
            } else if (type === 'buy_x_get_y') {
                valueLabel.textContent = 'Buy Quantity';
                valueInput.placeholder = 'e.g., 2 for Buy 2 Get 1';
                valueInput.onkeyup = null;
                buyXGetYField.style.display = 'block';
            }
        }

        // Initialize field states
        document.addEventListener('DOMContentLoaded', function() {
            toggleDiscountFields();
        });

        // Form submission - convert formatted prices to numbers
        document.querySelector('form').addEventListener('submit', function(e) {
            const rupiahFields = ['min_purchase_amount', 'max_discount_amount'];
            
            rupiahFields.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field && field.value) {
                    field.value = field.value.replace(/[^\d]/g, '');
                }
            });

            // Convert value field if it's fixed_amount
            const typeField = document.getElementById('type');
            const valueField = document.getElementById('value');
            if (typeField.value === 'fixed_amount' && valueField.value) {
                valueField.value = valueField.value.replace(/[^\d]/g, '');
            }
        });
    </script>
@endsection
