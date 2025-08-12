@extends('admin.layout')

@section('title', 'Stock Movements')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Stock Movements</h1>
                    <a href="{{ route('admin.inventory.index') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Back to Inventory
                    </a>
                </div>

                <!-- Stock Movements Table -->
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Previous</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">New</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($movements as $movement)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $movement->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-8 w-8 rounded object-cover" 
                                             src="{{ $movement->product->image_url ?? 'https://via.placeholder.com/32' }}" 
                                             alt="{{ $movement->product->name }}">
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $movement->product->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @switch($movement->type)
                                            @case('add') bg-green-100 text-green-800 @break
                                            @case('subtract') bg-red-100 text-red-800 @break
                                            @case('set') bg-blue-100 text-blue-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                        {{ $movement->formatted_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium
                                    @switch($movement->type)
                                        @case('add') text-green-600 @break
                                        @case('subtract') text-red-600 @break
                                        @default text-gray-900
                                    @endswitch">
                                    @if($movement->type === 'add') + @elseif($movement->type === 'subtract') - @endif
                                    {{ $movement->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $movement->previous_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ $movement->new_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $movement->user->name ?? 'System' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $movement->reason ?: 'No reason provided' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No stock movements recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($movements->hasPages())
                <div class="mt-4">
                    {{ $movements->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
