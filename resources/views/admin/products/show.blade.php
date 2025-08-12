@extends('admin.layout')

@section('title', 'Product Details')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Product Details</h1>
                <p class="text-gray-600 mt-1">View and manage product information</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 transition ease-in-out duration-150">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Products
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Product Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Image and Basic Info -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        @if($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" 
                                 class="w-full h-64 object-cover rounded-lg shadow-md">
                        @else
                            <div class="w-full h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-image text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-500">No image available</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $product->name }}</h2>
                            <div class="flex items-center space-x-4 mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas fa-circle text-xs mr-1"></i>
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($product->is_featured)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-star text-xs mr-1"></i>
                                        Featured
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-1">Price</h3>
                                <p class="text-2xl font-bold text-primary-600">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-1">Stock</h3>
                                <p class="text-2xl font-bold {{ $product->stock_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $product->stock_quantity }}
                                </p>
                            </div>
                        </div>
                        
                        @if($product->sku)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-1">SKU</h3>
                                <p class="text-lg font-mono text-gray-900">{{ $product->sku }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($product->description)
                <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-align-left mr-2 text-primary-600"></i>
                        Description
                    </h3>
                    <div class="prose prose-gray max-w-none">
                        <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Categories -->
            @if($product->categories->count() > 0)
                <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-tags mr-2 text-primary-600"></i>
                        Categories
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($product->categories as $category)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                                {{ $category->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Product Variants -->
            @if($product->variants->count() > 0)
                <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-layer-group mr-2 text-primary-600"></i>
                        Product Variants
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($product->variants as $variant)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $variant->name }}: {{ $variant->value }}</h4>
                                        @if($variant->unit)
                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">{{ strtoupper($variant->unit) }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <span class="text-xs text-gray-500">Price</span>
                                        <p class="text-sm font-semibold text-primary-600">
                                            @if($variant->price)
                                                Rp {{ number_format($variant->price, 0, ',', '.') }}
                                            @else
                                                <span class="text-gray-400">Use base price</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">Stock</span>
                                        <p class="text-sm font-semibold {{ ($variant->stock ?? $product->stock_quantity) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $variant->stock ?? $product->stock_quantity }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-cog mr-2 text-primary-600"></i>
                    Quick Actions
                </h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.products.edit', $product) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Product
                    </a>
                    
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 transition ease-in-out duration-150">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Product
                        </button>
                    </form>
                </div>
            </div>

            <!-- Product Stats -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-chart-bar mr-2 text-primary-600"></i>
                    Product Information
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Product ID</span>
                        <span class="text-sm font-semibold text-gray-900">#{{ $product->id }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Created</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $product->created_at->format('M d, Y') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Last Updated</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $product->updated_at->format('M d, Y') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Categories</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $product->categories->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Stock Status -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-boxes mr-2 text-primary-600"></i>
                    Stock Status
                </h3>
                <div class="text-center">
                    @if($product->stock_quantity > 20)
                        <div class="mb-2">
                            <i class="fas fa-check-circle text-4xl text-green-500"></i>
                        </div>
                        <p class="text-green-600 font-semibold">In Stock</p>
                        <p class="text-sm text-gray-600">{{ $product->stock_quantity }} units available</p>
                    @elseif($product->stock_quantity > 0)
                        <div class="mb-2">
                            <i class="fas fa-exclamation-triangle text-4xl text-yellow-500"></i>
                        </div>
                        <p class="text-yellow-600 font-semibold">Low Stock</p>
                        <p class="text-sm text-gray-600">Only {{ $product->stock_quantity }} units left</p>
                    @else
                        <div class="mb-2">
                            <i class="fas fa-times-circle text-4xl text-red-500"></i>
                        </div>
                        <p class="text-red-600 font-semibold">Out of Stock</p>
                        <p class="text-sm text-gray-600">No units available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
