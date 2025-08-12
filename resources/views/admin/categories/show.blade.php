@extends('admin.layout')

@section('title', 'Category Details')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Category: {{ $category->name }}</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.categories.edit', $category) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('admin.categories.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Categories
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Category Details -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Category Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $category->name }}</p>
                    </div>

                    @if($category->description)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $category->description }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Slug</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $category->slug }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    @if($category->meta_title)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $category->meta_title }}</p>
                    </div>
                    @endif

                    @if($category->meta_description)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $category->meta_description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Products in this Category -->
            @if($category->products->count() > 0)
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Products in this Category ({{ $category->products->count() }})</h2>
                
                <div class="space-y-4">
                    @foreach($category->products as $product)
                        <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ $product->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ Str::limit($product->description, 100) }}</p>
                                    <div class="mt-2 flex items-center space-x-4">
                                        <span class="text-sm font-medium text-green-600">${{ number_format($product->price, 2) }}</span>
                                        <span class="text-sm text-gray-500">Stock: {{ $product->stock_quantity }}</span>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('admin.products.show', $product) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                                    View Product
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Products</h2>
                <p class="text-gray-500">No products in this category yet.</p>
            </div>
            @endif
        </div>

        <!-- Category Stats -->
        <div class="space-y-6">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Statistics</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Products</label>
                        <p class="text-2xl font-bold text-green-600">{{ $category->products->count() }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Active Products</label>
                        <p class="text-2xl font-bold text-blue-600">{{ $category->products->where('is_active', true)->count() }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created</label>
                        <p class="text-sm text-gray-900">{{ $category->created_at->format('M d, Y g:i A') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <p class="text-sm text-gray-900">{{ $category->updated_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                
                <div class="space-y-2">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                        Edit Category
                    </a>
                    
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                            Delete Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
