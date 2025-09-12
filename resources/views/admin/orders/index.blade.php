@extends('admin.layout')

@section('title', 'Orders')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Orders</h1>
            <a href="{{ route('admin.orders.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Add New Order
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4 bg-white p-4 rounded-md shadow flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-600 mb-1">Search</label>
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Order #, Invoice, Resi, Customer..." class="border rounded px-3 py-2 w-64">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Status</label>
            <select name="status" class="border rounded px-3 py-2">
                <option value="">All</option>
                @foreach(['pending','processing','shipped','completed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(($status ?? '') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Payment</label>
            <select name="payment" class="border rounded px-3 py-2">
                <option value="">All</option>
                @foreach(['pending','paid','failed','refunded'] as $p)
                    <option value="{{ $p }}" @selected(($payment ?? '') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">Filter</button>
        </div>
        @if(($q ?? false) || ($status ?? false) || ($payment ?? false))
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600">Reset</a>
        </div>
        @endif
    </form>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($orders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">
                                    #{{ $order->invoice_no ?? $order->external_id ?? $order->id }}
                                </a>
                            </div>
                            <div class="mt-1 text-xs">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                <span class="text-gray-300">•</span>
                                <a href="{{ route('admin.orders.edit', $order) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                <span class="text-gray-300">•</span>
                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this order?')">Delete</button>
                                </form>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 flex-shrink-0">
                                    @if($order->user && $order->user->profile_photo_path)
                                        <img class="h-8 w-8 rounded-full" src="{{ Storage::url($order->user->profile_photo_path) }}" alt="{{ $order->user->name }}">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-700">{{ $order->user ? strtoupper(substr($order->user->name, 0, 1)) : 'G' }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $order->user ? $order->user->name : 'Guest' }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->user ? $order->user->email : ($order->customer_email ?? '-') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ optional($order->orderProducts)->sum('quantity') ?? 0 }} items
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">Rp {{ number_format((float)($order->total ?? $order->total_amount ?? 0), 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @if($order->status === 'completed' || $order->status === 'paid') bg-green-100 text-green-800
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @if($order->payment_status === 'paid') bg-green-100 text-green-800
                                @elseif($order->payment_status === 'failed') bg-red-100 text-red-800
                                @elseif($order->payment_status === 'refunded') bg-purple-100 text-purple-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                <a href="{{ route('admin.orders.edit', $order) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this order?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No orders found. <a href="{{ route('admin.orders.create') }}" class="text-green-600 hover:text-green-900">Create the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->appends(['q' => $q, 'status' => $status, 'payment' => $payment])->links() }}
        </div>
    @endif
@endsection
