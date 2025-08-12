@extends('admin.layout')

@section('title', 'Edit Product')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Product: {{ $product->name }}</h1>
                <p class="text-gray-600 mt-1">Update product information</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.products.show', $product) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 transition ease-in-out duration-150">
                    <i class="fas fa-eye mr-2"></i>
                    View
                </a>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Products
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-xl p-8 border border-gray-100">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Product Name*</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('name') border-red-500 @enderror"
                               placeholder="Enter product name">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="5"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('description') border-red-500 @enderror"
                                  placeholder="Enter product description">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">Price*</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="text" id="price" name="price" value="{{ old('price', number_format($product->price, 0, ',', '.')) }}" required
                                       class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('price') border-red-500 @enderror"
                                       placeholder="0" onkeyup="formatRupiah(this)">
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="stock_quantity" class="block text-sm font-semibold text-gray-700 mb-2">Stock Quantity*</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('stock_quantity') border-red-500 @enderror"
                                   placeholder="0">
                            @error('stock_quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="sku" class="block text-sm font-semibold text-gray-700 mb-2">SKU</label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('sku') border-red-500 @enderror"
                               placeholder="Product SKU">
                        @error('sku')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div>
                        <label for="image_path" class="block text-sm font-semibold text-gray-700 mb-2">Product Image</label>
                        
                        @if($product->image_path)
                            <div class="mb-4">
                                <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover rounded-lg shadow-md">
                                <p class="text-xs text-gray-500 mt-1">Current image</p>
                            </div>
                        @endif
                        
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition duration-150">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <input type="file" id="image_path" name="image_path" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 @error('image_path') border-red-500 @enderror">
                            <p class="mt-2 text-xs text-gray-500">PNG, JPG, JPEG up to 2MB (leave blank to keep current image)</p>
                        </div>
                        @error('image_path')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Categories</label>
                        <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-4 space-y-2">
                            @forelse($categories as $category)
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                           {{ in_array($category->id, old('categories', $product->categories->pluck('id')->toArray())) ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">No categories available. <a href="{{ route('admin.categories.create') }}" class="text-primary-600 hover:text-primary-700">Create one first</a>.</p>
                            @endforelse
                        </div>
                        @error('categories')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">Product is active</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="is_featured" class="ml-3 text-sm font-medium text-gray-700">Featured product</label>
                        </div>
                    </div>

                    <!-- Product Variants -->
                    <div class="border-t pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <label class="block text-sm font-semibold text-gray-700">Product Variants</label>
                            <button type="button" id="addVariant" class="inline-flex items-center px-3 py-1 bg-primary-100 text-primary-700 rounded-lg text-xs font-medium hover:bg-primary-200 transition duration-150">
                                <i class="fas fa-plus mr-1"></i>
                                Add Variant
                            </button>
                        </div>
                        
                        <div id="variantsContainer" class="space-y-4">
                            @foreach($product->variants as $index => $variant)
                                <div class="variant-row bg-gray-50 p-4 rounded-lg border border-gray-200" data-index="{{ $index }}">
                                    <div class="flex justify-between items-start mb-4">
                                        <h4 class="text-sm font-semibold text-gray-700">Variant {{ $index + 1 }}</h4>
                                        <button type="button" class="remove-variant text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Variant Name</label>
                                            <input type="text" name="variants[{{ $index }}][name]" 
                                                   value="{{ old('variants.' . $index . '.name', $variant->name) }}"
                                                   placeholder="e.g., Size, Weight"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Variant Value</label>
                                            <input type="text" name="variants[{{ $index }}][value]" 
                                                   value="{{ old('variants.' . $index . '.value', $variant->value) }}"
                                                   placeholder="e.g., Large, 500g"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
                                            <select name="variants[{{ $index }}][unit]" 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                                                <option value="">Select Unit</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit }}" {{ old('variants.' . $index . '.unit', $variant->unit) == $unit ? 'selected' : '' }}>
                                                        {{ ucfirst($unit) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Price Override (Rp)</label>
                                            <input type="text" name="variants[{{ $index }}][price]" 
                                                   value="{{ $variant->price ? number_format($variant->price, 0, ',', '.') : '' }}"
                                                   placeholder="Optional price"
                                                   onkeyup="formatRupiah(this)"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Stock Override</label>
                                            <input type="number" name="variants[{{ $index }}][stock]" 
                                                   value="{{ old('variants.' . $index . '.stock', $variant->stock) }}"
                                                   placeholder="Optional stock"
                                                   min="0"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @error('variants')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-8 border-t border-gray-200 mt-8">
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 active:from-primary-600 active:to-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                    <i class="fas fa-save mr-2"></i>
                    Update Product
                </button>
            </div>
        </form>
    </div>

    <script>
        let variantIndex = {{ $product->variants->count() }};
        
        // Rupiah formatting function
        function formatRupiah(input) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value).toLocaleString('id-ID');
                input.value = value;
            }
        }
        
        // Add variant functionality
        document.getElementById('addVariant').addEventListener('click', function() {
            const container = document.getElementById('variantsContainer');
            const variantHtml = `
                <div class="variant-row bg-gray-50 p-4 rounded-lg border border-gray-200" data-index="${variantIndex}">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-sm font-semibold text-gray-700">Variant ${variantIndex + 1}</h4>
                        <button type="button" class="remove-variant text-red-500 hover:text-red-700">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Variant Name</label>
                            <input type="text" name="variants[${variantIndex}][name]" 
                                   placeholder="e.g., Size, Weight"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Variant Value</label>
                            <input type="text" name="variants[${variantIndex}][value]" 
                                   placeholder="e.g., Large, 500g"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
                            <select name="variants[${variantIndex}][unit]" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Select Unit</option>
                                <option value="kg">Kg</option>
                                <option value="g">Gram</option>
                                <option value="l">Liter</option>
                                <option value="ml">Mililiter</option>
                                <option value="pcs">Pieces</option>
                                <option value="pack">Pack</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Price Override (Rp)</label>
                            <input type="text" name="variants[${variantIndex}][price]" 
                                   placeholder="Optional price"
                                   onkeyup="formatRupiah(this)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Stock Override</label>
                            <input type="number" name="variants[${variantIndex}][stock]" 
                                   placeholder="Optional stock"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', variantHtml);
            variantIndex++;
            
            // Add remove functionality to the new variant
            const newVariant = container.lastElementChild;
            newVariant.querySelector('.remove-variant').addEventListener('click', function() {
                newVariant.remove();
            });
        });
        
        // Add remove functionality to existing variants
        document.querySelectorAll('.remove-variant').forEach(button => {
            button.addEventListener('click', function() {
                button.closest('.variant-row').remove();
            });
        });
        
        // Form submission - convert formatted prices to numbers
        document.querySelector('form').addEventListener('submit', function(e) {
            // Convert main price
            const priceInput = document.getElementById('price');
            if (priceInput.value) {
                priceInput.value = priceInput.value.replace(/[^\d]/g, '');
            }
            
            // Convert variant prices
            const variantPrices = document.querySelectorAll('input[name*="[price]"]');
            variantPrices.forEach(input => {
                if (input.value) {
                    input.value = input.value.replace(/[^\d]/g, '');
                }
            });
        });
    </script>
@endsection
