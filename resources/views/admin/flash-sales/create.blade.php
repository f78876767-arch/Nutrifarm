@extends('admin.layout')

@section('title', 'Create Flash Sale')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Create Flash Sale</h1>
                    <a href="{{ route('admin.flash-sales.index') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Back to Flash Sales
                    </a>
                </div>

                <form method="POST" action="{{ route('admin.flash-sales.store') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Product Selection -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Product Selection</h3>
                        
                        <div>
                            <label for="products" class="block text-sm font-medium text-gray-700 mb-2">Select Products*</label>
                            <div class="relative">
                                <select id="products" name="products[]" multiple required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('products') border-red-500 @enderror"
                                        style="min-height: 120px;">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                {{ in_array($product->id, old('products', [])) ? 'selected' : '' }} 
                                                data-price="{{ $product->price }}"
                                                data-stock="{{ $product->stock_quantity }}"
                                                data-image="{{ $product->image_url }}">
                                            {{ $product->name }} - ${{ number_format($product->price, 2) }} (Stock: {{ $product->stock_quantity ?? 0 }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple products</p>
                            </div>
                            @error('products')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            
                            <!-- Selected Products Preview -->
                            <div id="selectedProductsPreview" class="hidden mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Selected Products:</h4>
                                <div id="selectedProductsList" class="space-y-2"></div>
                            </div>
                                        <p class="text-sm text-gray-500">Original Price: <span id="originalPrice"></span></p>
                                        <p class="text-sm text-gray-500">Available Stock: <span id="stockQuantity"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Configuration -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Discount Configuration</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="discount_percentage" class="block text-sm font-medium text-gray-700 mb-2">Discount Percentage*</label>
                                <div class="relative">
                                    <input type="number" step="0.01" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage') }}" required min="0.01" max="99.99"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('discount_percentage') border-red-500 @enderror"
                                           placeholder="25.00">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                                @error('discount_percentage')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Percentage discount on original price</p>
                            </div>

                            <div>
                                <label for="max_discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Max Discount Amount</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" step="0.01" id="max_discount_amount" name="max_discount_amount" value="{{ old('max_discount_amount') }}" min="0"
                                           class="w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_discount_amount') border-red-500 @enderror"
                                           placeholder="50.00">
                                </div>
                                @error('max_discount_amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Maximum discount cap (optional)</p>
                            </div>

                            <div>
                                <label for="max_quantity" class="block text-sm font-medium text-gray-700 mb-2">Max Sale Quantity</label>
                                <input type="number" id="max_quantity" name="max_quantity" value="{{ old('max_quantity') }}" min="1"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_quantity') border-red-500 @enderror"
                                       placeholder="100">
                                @error('max_quantity')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Total items for all products (optional)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-2">Start Date & Time*</label>
                                <input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at') }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('starts_at') border-red-500 @enderror">
                                @error('starts_at')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-2">End Date & Time*</label>
                                <input type="datetime-local" id="ends_at" name="ends_at" value="{{ old('ends_at') }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('ends_at') border-red-500 @enderror">
                                @error('ends_at')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Duration Display -->
                        <div id="durationInfo" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md hidden">
                            <p class="text-sm text-blue-800">
                                <strong>Sale Duration:</strong> <span id="saleDuration"></span>
                            </p>
                        </div>
                        
                        <!-- Quick Duration Buttons -->
                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Quick Duration (from now):</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="setDuration(1)" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-full">1 Hour</button>
                                <button type="button" onclick="setDuration(3)" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-full">3 Hours</button>
                                <button type="button" onclick="setDuration(6)" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-full">6 Hours</button>
                                <button type="button" onclick="setDuration(12)" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-full">12 Hours</button>
                                <button type="button" onclick="setDuration(24)" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-full">1 Day</button>
                                <button type="button" onclick="setDuration(48)" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-full">2 Days</button>
                            </div>
                        </div>
                    </div>

                    <!-- Marketing -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Marketing & Description</h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Flash Sale Title*</label>
                                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-500 @enderror"
                                       placeholder="e.g., Lightning Deal: 50% Off Premium Products">
                                @error('title')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                                      placeholder="Describe this flash sale offer and create urgency...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Settings</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Activate flash sale immediately
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-6">
                        <a href="{{ route('admin.flash-sales.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Create Flash Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tips Card -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Flash Sale Best Practices</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Offer at least 20-30% discount for significant impact</li>
                            <li>Limit quantity to create urgency and scarcity</li>
                            <li>Choose high-demand or seasonal products</li>
                            <li>Schedule during peak traffic hours (evenings/weekends)</li>
                            <li>Keep duration short (1-24 hours) for urgency</li>
                            <li>Set reasonable per-customer limits to prevent hoarding</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productsSelect = document.getElementById('products');
    const discountPercentageInput = document.getElementById('discount_percentage');
    const startsAtInput = document.getElementById('starts_at');
    const endsAtInput = document.getElementById('ends_at');
    
    const selectedProductsPreview = document.getElementById('selectedProductsPreview');
    const selectedProductsList = document.getElementById('selectedProductsList');
    const durationInfo = document.getElementById('durationInfo');
    
    // Product selection handling
    productsSelect.addEventListener('change', function() {
        const selectedOptions = Array.from(this.selectedOptions);
        
        if (selectedOptions.length > 0) {
            selectedProductsList.innerHTML = '';
            
            selectedOptions.forEach(option => {
                const price = parseFloat(option.dataset.price);
                const stock = option.dataset.stock;
                const image = option.dataset.image;
                const name = option.text.split(' - ')[0];
                
                const productDiv = document.createElement('div');
                productDiv.className = 'flex items-center p-2 bg-white border border-gray-200 rounded-md';
                
                const discountPrice = discountPercentageInput.value ? 
                    price * (1 - discountPercentageInput.value / 100) : price;
                
                productDiv.innerHTML = `
                    <img class="h-12 w-12 rounded object-cover" src="${image || 'https://via.placeholder.com/48'}" alt="">
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-medium text-gray-900">${name}</h4>
                        <p class="text-sm text-gray-500">
                            Original: $${price.toFixed(2)} → 
                            Sale: $${discountPrice.toFixed(2)} 
                            (Stock: ${stock})
                        </p>
                    </div>
                `;
                
                selectedProductsList.appendChild(productDiv);
            });
            
            selectedProductsPreview.classList.remove('hidden');
        } else {
            selectedProductsPreview.classList.add('hidden');
        }
    });
    
    // Update product previews when discount changes
    discountPercentageInput.addEventListener('input', function() {
        if (productsSelect.selectedOptions.length > 0) {
            productsSelect.dispatchEvent(new Event('change'));
        }
    });
    
    // Update duration information
    function updateDuration() {
        if (startsAtInput.value && endsAtInput.value) {
            const start = new Date(startsAtInput.value);
            const end = new Date(endsAtInput.value);
            const diff = end - start;
            
            if (diff > 0) {
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const days = Math.floor(hours / 24);
                const remainingHours = hours % 24;
                
                let durationText = '';
                if (days > 0) {
                    durationText = `${days} day${days > 1 ? 's' : ''}`;
                    if (remainingHours > 0) {
                        durationText += ` and ${remainingHours} hour${remainingHours > 1 ? 's' : ''}`;
                    }
                } else {
                    durationText = `${hours} hour${hours !== 1 ? 's' : ''}`;
                }
                
                document.getElementById('saleDuration').textContent = durationText;
                durationInfo.classList.remove('hidden');
            } else {
                durationInfo.classList.add('hidden');
            }
        }
    }
    
    startsAtInput.addEventListener('change', updateDuration);
    endsAtInput.addEventListener('change', updateDuration);
    
    // Set default start time to current time
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    startsAtInput.value = now.toISOString().slice(0, 16);
});

function setDuration(hours) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    
    const end = new Date(now.getTime() + (hours * 60 * 60 * 1000));
    
    document.getElementById('starts_at').value = now.toISOString().slice(0, 16);
    document.getElementById('ends_at').value = end.toISOString().slice(0, 16);
    
    // Trigger duration update
    document.getElementById('starts_at').dispatchEvent(new Event('change'));
}
</script>
@endsection
