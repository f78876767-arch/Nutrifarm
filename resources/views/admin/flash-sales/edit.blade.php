@extends('admin.layout')

@section('title', 'Edit Flash Sale')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Flash Sale: {{ $flashSale->title }}</h1>
                <p class="text-gray-600 mt-1">Update flash sale information and settings</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.flash-sales.show', $flashSale) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 transition ease-in-out duration-150">
                    <i class="fas fa-eye mr-2"></i>
                    View
                </a>
                <a href="{{ route('admin.flash-sales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Flash Sales
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-xl p-8 border border-gray-100">
        <form method="POST" action="{{ route('admin.flash-sales.update', $flashSale) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Flash Sale Title*</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $flashSale->title) }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('title') border-red-500 @enderror"
                               placeholder="Enter flash sale title">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('description') border-red-500 @enderror"
                                  placeholder="Enter flash sale description">{{ old('description', $flashSale->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="discount_percentage" class="block text-sm font-semibold text-gray-700 mb-2">Discount Percentage*</label>
                            <div class="relative">
                                <input type="number" id="discount_percentage" name="discount_percentage" 
                                       value="{{ old('discount_percentage', $flashSale->discount_percentage) }}" required
                                       min="0" max="100" step="0.01"
                                       class="w-full px-4 py-3 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('discount_percentage') border-red-500 @enderror"
                                       placeholder="0.00">
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">%</span>
                            </div>
                            @error('discount_percentage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_discount_amount" class="block text-sm font-semibold text-gray-700 mb-2">Maximum Discount (Rp)</label>
                            <input type="text" id="max_discount_amount" name="max_discount_amount" 
                                   value="{{ $flashSale->max_discount_amount ? number_format($flashSale->max_discount_amount, 0, ',', '.') : '' }}"
                                   onkeyup="formatRupiah(this)"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150"
                                   placeholder="Optional maximum discount">
                            @error('max_discount_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="max_quantity" class="block text-sm font-semibold text-gray-700 mb-2">Maximum Quantity per Customer</label>
                        <input type="number" id="max_quantity" name="max_quantity" value="{{ old('max_quantity', $flashSale->max_quantity) }}" min="1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150"
                               placeholder="Optional quantity limit per customer">
                        @error('max_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="starts_at" class="block text-sm font-semibold text-gray-700 mb-2">Start Date & Time*</label>
                            <input type="datetime-local" id="starts_at" name="starts_at" 
                                   value="{{ old('starts_at', $flashSale->starts_at->format('Y-m-d\TH:i')) }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('starts_at') border-red-500 @enderror">
                            @error('starts_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ends_at" class="block text-sm font-semibold text-gray-700 mb-2">End Date & Time*</label>
                            <input type="datetime-local" id="ends_at" name="ends_at" 
                                   value="{{ old('ends_at', $flashSale->ends_at->format('Y-m-d\TH:i')) }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('ends_at') border-red-500 @enderror">
                            @error('ends_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-red-800 mb-4">
                            <i class="fas fa-fire mr-2"></i>
                            Flash Sale Preview
                        </h3>
                        <div id="previewContent">
                            <div class="mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <span id="previewPercentage">{{ $flashSale->discount_percentage }}%</span> OFF
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">Example: Product with price Rp 100,000</p>
                            <div class="flex items-center space-x-2 mt-2">
                                <span class="text-sm text-gray-500 line-through">Rp 100,000</span>
                                <span class="text-lg font-bold text-red-600" id="previewPrice">
                                    Rp {{ number_format(100000 * (1 - $flashSale->discount_percentage / 100), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Apply to Products</label>
                        <div class="max-h-80 overflow-y-auto border border-gray-300 rounded-lg p-4 space-y-2">
                            @forelse($products as $product)
                                <label class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                                    <input type="checkbox" name="products[]" value="{{ $product->id }}" 
                                           {{ in_array($product->id, old('products', $flashSale->products->pluck('id')->toArray())) ? 'checked' : '' }}
                                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    <div class="flex items-center space-x-3 flex-1">
                                        @if($product->image_path)
                                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded object-cover">
                                        @else
                                            <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400 text-sm"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="text-sm text-gray-700 font-medium">{{ $product->name }}</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                                <span class="text-xs text-red-600 font-medium">
                                                    → Rp {{ number_format($product->price * (1 - $flashSale->discount_percentage / 100), 0, ',', '.') }}
                                                </span>
                                            </div>
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
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $flashSale->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">Flash Sale is active</label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-8 border-t border-gray-200 mt-8">
                <a href="{{ route('admin.flash-sales.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-red-600 hover:to-red-700 active:from-red-600 active:to-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                    <i class="fas fa-fire mr-2"></i>
                    Update Flash Sale
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

        // Update preview when discount percentage changes
        document.getElementById('discount_percentage').addEventListener('input', function() {
            const percentage = this.value || 0;
            const originalPrice = 100000;
            const discountedPrice = originalPrice * (1 - percentage / 100);
            
            document.getElementById('previewPercentage').textContent = percentage + '%';
            document.getElementById('previewPrice').textContent = 'Rp ' + discountedPrice.toLocaleString('id-ID');
            
            // Update all product previews
            updateProductPreviews(percentage);
        });

        function updateProductPreviews(discountPercentage) {
            const productLabels = document.querySelectorAll('label:has(input[name="products[]"])');
            productLabels.forEach(label => {
                const priceElements = label.querySelectorAll('.text-red-600');
                if (priceElements.length > 0) {
                    const originalPriceText = label.querySelector('.text-gray-500').textContent;
                    const originalPrice = parseInt(originalPriceText.replace(/[^\d]/g, ''));
                    const discountedPrice = originalPrice * (1 - discountPercentage / 100);
                    
                    priceElements[0].textContent = '→ Rp ' + discountedPrice.toLocaleString('id-ID');
                }
            });
        }

        // Form submission - convert formatted prices to numbers
        document.querySelector('form').addEventListener('submit', function(e) {
            const maxDiscountField = document.getElementById('max_discount_amount');
            if (maxDiscountField.value) {
                maxDiscountField.value = maxDiscountField.value.replace(/[^\d]/g, '');
            }
        });
    </script>
@endsection
