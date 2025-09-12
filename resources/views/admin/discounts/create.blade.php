@extends('admin.layout')

@section('title', 'Create Discount')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Create New Discount</h1>
                    <a href="{{ route('admin.discounts.index') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Back to Discounts
                    </a>
                </div>

                <form method="POST" action="{{ route('admin.discounts.store') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Discount Name*</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                                       placeholder="e.g., Summer Sale 2024">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Generated Code</label>
                                <input type="text" id="code-preview" readonly
                                       class="w-full rounded-md border-gray-200 bg-gray-100 text-gray-600 shadow-sm"
                                       placeholder="Auto">
                                <p class="text-xs text-gray-500 mt-1">Auto from name</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Discount Type*</label>
                            <select id="type" name="type" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('type') border-red-500 @enderror">
                                <option value="">Select Type</option>
                                <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage Discount</option>
                                <option value="fixed_amount" {{ old('type') === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="buy_x_get_y" {{ old('type') === 'buy_x_get_y' ? 'selected' : '' }}>Buy X Get Y</option>
                            </select>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                                      placeholder="Brief description of the discount offer">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Discount Configuration -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Discount Configuration</h3>
                        
                        <div id="percentage-fields" class="hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Discount Percentage*</label>
                                    <div class="relative">
                                        <input type="number" id="value" name="value" value="{{ old('value') }}" min="0" max="100" step="0.01"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-8 @error('value') border-red-500 @enderror"
                                               placeholder="20">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">%</span>
                                        </div>
                                    </div>
                                    @error('value')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="max_discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Maximum Discount Amount</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" id="max_discount_amount" name="max_discount_amount" value="{{ old('max_discount_amount') }}" min="0" step="0.01"
                                               class="w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_discount_amount') border-red-500 @enderror"
                                               placeholder="100.00">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Optional: Cap the maximum discount amount</p>
                                    @error('max_discount_amount')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div id="fixed-fields" class="hidden">
                            <div>
                                <label for="value_fixed" class="block text-sm font-medium text-gray-700 mb-2">Discount Amount*</label>
                                <div class="relative max-w-xs">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" id="value_fixed" name="value_fixed" value="{{ old('value_fixed') }}" min="0" step="0.01"
                                           class="w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('value_fixed') border-red-500 @enderror"
                                           placeholder="20.00">
                                </div>
                                @error('value_fixed')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div id="buy-x-get-y-fields" class="hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="value_buy" class="block text-sm font-medium text-gray-700 mb-2">Buy Quantity*</label>
                                    <input type="number" id="value_buy" name="value_buy" value="{{ old('value_buy') }}" min="1"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('value_buy') border-red-500 @enderror"
                                           placeholder="2">
                                    @error('value_buy')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="get_quantity" class="block text-sm font-medium text-gray-700 mb-2">Get Quantity*</label>
                                    <input type="number" id="get_quantity" name="get_quantity" value="{{ old('get_quantity') }}" min="1"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('get_quantity') border-red-500 @enderror"
                                           placeholder="1">
                                    @error('get_quantity')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conditions -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Conditions & Limits</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="min_purchase_amount" class="block text-sm font-medium text-gray-700 mb-2">Minimum Purchase Amount</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" id="min_purchase_amount" name="min_purchase_amount" value="{{ old('min_purchase_amount') }}" min="0" step="0.01"
                                           class="w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('min_purchase_amount') border-red-500 @enderror"
                                           placeholder="50.00">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Minimum order value to apply discount</p>
                                @error('min_purchase_amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="usage_limit" class="block text-sm font-medium text-gray-700 mb-2">Usage Limit</label>
                                <input type="number" id="usage_limit" name="usage_limit" value="{{ old('usage_limit') }}" min="1"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('usage_limit') border-red-500 @enderror"
                                       placeholder="Leave blank for unlimited">
                                <p class="text-xs text-gray-500 mt-1">Total number of times this discount can be used</p>
                                @error('usage_limit')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('starts_at') border-red-500 @enderror">
                                <p class="text-xs text-gray-500 mt-1">Leave blank to start immediately</p>
                                @error('starts_at')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="datetime-local" id="ends_at" name="ends_at" value="{{ old('ends_at') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('ends_at') border-red-500 @enderror">
                                <p class="text-xs text-gray-500 mt-1">Leave blank for no expiration</p>
                                @error('ends_at')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Product Selection -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Apply to Products</h3>
                        
                        <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md p-3">
                            <div class="space-y-2">
                                @foreach($products as $product)
                                <label class="flex items-center">
                                    <input type="checkbox" name="products[]" value="{{ $product->id }}" 
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <div class="ml-3 flex items-center">
                                        <img class="h-8 w-8 rounded object-cover" 
                                             src="{{ $product->image_url ?? 'https://via.placeholder.com/32' }}" 
                                             alt="{{ $product->name }}">
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">${{ number_format($product->price, 2) }}</div>
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Leave unselected to apply to all products</p>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Activate discount immediately
                        </label>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-6">
                        <a href="{{ route('admin.discounts.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Create Discount
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const codePreview = document.getElementById('code-preview');
    function gen(str){
        let base = str.replace(/[^A-Za-z0-9]/g,'').toUpperCase().substring(0,6);
        if(base.length < 3) base = 'DISCNT';
        codePreview.value = base;
    }
    nameInput.addEventListener('input', ()=> gen(nameInput.value));
    gen(nameInput.value);
});
</script>
@endsection
