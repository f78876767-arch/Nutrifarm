@extends('admin.layout')

@section('title', 'Products - Nutrifarm Admin')
@section('header', 'Products')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Products</h1>
                <p class="text-gray-600 mt-1">Manage your product inventory</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 active:from-primary-600 active:to-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                <i class="fas fa-plus mr-2"></i>
                Add New Product
            </a>
        </div>
    </div>

<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <ul class="divide-y divide-gray-200">
        @forelse($products as $product)
            <li>
                <div class="px-4 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        @if($product->image_path)
                            <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}">
                        @else
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">{{ substr($product->name, 0, 2) }}</span>
                            </div>
                        @endif
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                            <div class="text-sm text-gray-500">
                                Rp {{ number_format($product->price, 0, ',', '.') }} • Stock: {{ $product->stock_quantity }}
                                @if($product->categories->count())
                                    • {{ $product->categories->pluck('name')->implode(', ') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($product->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Inactive
                            </span>
                        @endif
                        <a href="{{ route('admin.products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View</a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium" 
                                    onclick="return confirm('Are you sure you want to delete this product?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </li>
        @empty
            <li class="px-4 py-8 text-center">
                <div class="text-gray-500">No products found.</div>
                <a href="{{ route('admin.products.create') }}" class="mt-2 inline-flex items-center text-sm font-medium text-green-600 hover:text-green-500">
                    Create your first product
                </a>
            </li>
        @endforelse
    </ul>
</div>

@if($products->hasPages())
    <div class="mt-6">
        {{ $products->links() }}
    </div>
@endif
@endsection
