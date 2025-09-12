@extends('admin.layout')

@section('title', 'Create Product')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Product</h1>
                <p class="text-gray-600 mt-1">Add a new product to your inventory</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Products
            </a>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-xl p-8 border border-gray-100">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Product Name*</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
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
                                  placeholder="Enter product description">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">Price*</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="text" id="price" name="price" value="{{ old('price') }}" required
                                       class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('price') border-red-500 @enderror"
                                       placeholder="0" onkeyup="formatRupiah(this)">
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="sku" class="block text-sm font-semibold text-gray-700 mb-2">SKU</label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 @error('sku') border-red-500 @enderror"
                               placeholder="Product SKU">
                        @error('sku')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Diskon Produk (Nominal)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <label for="discount_amount" class="block text-sm font-semibold text-gray-700 mb-2">Potongan (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                    <input type="text" id="discount_amount" name="discount_amount" value="{{ old('discount_amount') }}" class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="0" onkeyup="formatRupiah(this)">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Isi nominal potongan. Kosongkan jika tidak ada diskon.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div>
                        <label for="image_path" class="block text-sm font-semibold text-gray-700 mb-2">Product Image</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition duration-150">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <input type="file" id="image_path" name="image_path" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 @error('image_path') border-red-500 @enderror">
                            <p class="mt-2 text-xs text-gray-500">PNG, JPG, JPEG up to 2MB</p>
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
                                           {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}
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
                            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">Product is active</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
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
                        <p class="text-xs text-gray-500 mb-3">Set stock per variant. Product stock will be calculated from variant stock.</p>
                        <div id="variantsContainer" class="space-y-4">
                            <!-- Variants will be added here -->
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
                    Create Product
                </button>
            </div>
        </form>
    </div>

    <!-- Helpful Tips -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Product Creation Tips:</strong>
                </p>
                <ul class="mt-2 text-sm text-blue-600 list-disc list-inside space-y-1">
                    <li>Use clear, descriptive names that customers will search for</li>
                    <li>Add detailed descriptions to help customers make informed decisions</li>
                    <li>High-quality images significantly improve conversion rates</li>
                    <li>Keep accurate stock quantities to avoid overselling</li>
                    <li>Assign relevant categories to help customers find your products</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        let variantIndex = 0;
        
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
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                            <label class="block text-xs font-medium text-gray-700 mb-1">Base Price (Rp)</label>
                            <input type="text" name="variants[${variantIndex}][base_price]" 
                                   placeholder="Variant price"
                                   onkeyup="formatRupiah(this)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Stock Quantity</label>
                            <input type="number" name="variants[${variantIndex}][stock_quantity]" 
                                   placeholder="Stock amount"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">SKU</label>
                            <input type="text" name="variants[${variantIndex}][sku]" 
                                   placeholder="Variant SKU"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Discount (Rp)</label>
                            <input type="text" name="variants[${variantIndex}][discount_amount]" 
                                   placeholder="Discount amount"
                                   onkeyup="formatRupiah(this)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Weight (kg)</label>
                            <input type="number" name="variants[${variantIndex}][weight]" 
                                   step="0.01"
                                   placeholder="Weight in kg"
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
        
        // Form submission - convert formatted prices to numbers
        document.querySelector('form').addEventListener('submit', function(e) {
            // Convert main price
            const priceInput = document.getElementById('price');
            if (priceInput.value) {
                priceInput.value = priceInput.value.replace(/[^\d]/g, '');
            }
            
            // Convert variant prices
            const variantPrices = document.querySelectorAll('input[name*="[base_price]"], input[name*="[discount_amount]"]');
            variantPrices.forEach(input => {
                if (input.value) {
                    input.value = input.value.replace(/[^\d]/g, '');
                }
            });
        });
        
        // Ensure discount amount numeric on submit
        document.querySelector('form').addEventListener('submit', function(){
            const da = document.getElementById('discount_amount');
            if (da && da.value) da.value = da.value.replace(/[^\d]/g,'');
        });
    </script>
@endsection
