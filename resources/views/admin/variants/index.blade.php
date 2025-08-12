@extends('admin.layout')

@section('title', 'Product Variants')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Product Variants</h1>
                <p class="text-gray-600 mt-1">Manage product variations and options</p>
            </div>
            <a href="{{ route('admin.variants.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Create Variant
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Variant Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Value</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($variants as $variant)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($variant->product->image_path)
                                        <img src="{{ Storage::url($variant->product->image_path) }}" alt="{{ $variant->product->name }}" class="w-10 h-10 rounded-lg object-cover mr-3">
                                    @else
                                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $variant->product->name }}</div>
                                        <div class="text-sm text-gray-500">ID: {{ $variant->product->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $variant->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $variant->value }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($variant->unit)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ strtoupper($variant->unit) }}
                                    </span>
                                @elseif($variant->custom_unit)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ $variant->custom_unit }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($variant->price)
                                    <span class="font-semibold">Rp {{ number_format($variant->price, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-gray-400 italic">Use product price</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($variant->stock !== null)
                                    <span class="font-semibold {{ $variant->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $variant->stock }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Use product stock</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.variants.show', $variant) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.variants.edit', $variant) }}" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.variants.destroy', $variant) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this variant?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-layer-group text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-gray-500 text-lg font-medium">No variants found</p>
                                    <p class="text-gray-400 text-sm mt-1">Create your first product variant to get started</p>
                                    <a href="{{ route('admin.variants.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-primary-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-600 transition ease-in-out duration-150">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create Variant
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($variants->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $variants->links() }}
            </div>
        @endif
    </div>
@endsection
