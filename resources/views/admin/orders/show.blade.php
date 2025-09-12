@extends('admin.layout')

@section('title', 'Order Details')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->invoice_no ?? $order->external_id ?? $order->id }}</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.orders.edit', $order) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Edit Status
                </a>
                <a href="{{ route('admin.orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Orders
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->invoice_no ?? $order->external_id ?? $order->id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('d M Y H:i') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $order->user ? $order->user->name : 'Guest Customer' }}
                            @if($order->customer_email)
                                <br>{{ $order->customer_email }}
                            @elseif($order->user)
                                <br>{{ $order->user->email }}
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @if($order->status === 'completed' || $order->status === 'paid') bg-green-100 text-green-800
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @if($order->payment_status === 'paid') bg-green-100 text-green-800
                                @elseif($order->payment_status === 'failed') bg-red-100 text-red-800
                                @elseif($order->payment_status === 'refunded') bg-purple-100 text-purple-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($order->notes)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $order->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Order Items -->
            @if($order->orderProducts && $order->orderProducts->count() > 0)
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Items</h2>
                
                <div class="space-y-4">
                    @foreach($order->orderProducts as $item)
                        <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center">
                                    @if($item->product && $item->product->image_path)
                                        <img class="h-16 w-16 rounded object-cover mr-4" src="{{ Storage::url($item->product->image_path) }}" alt="{{ $item->product->name }}">
                                    @endif
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">{{ $item->product ? $item->product->name : 'Product not found' }}</h3>
                                        <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                                        <p class="text-sm text-gray-600">Unit Price: Rp {{ number_format((float)$item->price, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-medium text-gray-900">Rp {{ number_format((float)($item->price * $item->quantity), 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Items</h2>
                <p class="text-gray-500">No items found for this order.</p>
            </div>
            @endif
        </div>

        <!-- Right side panels -->
        <div class="space-y-6">
            <!-- Order Summary (kept) -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Order Summary</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Subtotal</span>
                        <span class="text-sm text-gray-900">Rp {{ number_format((float)($order->subtotal_amount ?? $order->total ?? 0), 0, ',', '.') }}</span>
                    </div>

                    @if(($order->tax_amount ?? 0) > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Tax</span>
                        <span class="text-sm text-gray-900">Rp {{ number_format((float)$order->tax_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    @if(($order->shipping_amount ?? 0) > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Shipping</span>
                        <span class="text-sm text-gray-900">Rp {{ number_format((float)$order->shipping_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    @if(($order->discount_amount ?? 0) > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Discount</span>
                        <span class="text-sm text-red-600">- Rp {{ number_format((float)$order->discount_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    <div class="border-t pt-3">
                        <div class="flex justify-between">
                            <span class="text-base font-medium text-gray-900">Total</span>
                            <span class="text-base font-bold text-gray-900">Rp {{ number_format((float)($order->total ?? $order->total_amount ?? 0), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment & Invoice -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Payment & Invoice</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Payment Status</span><span class="font-medium">{{ ucfirst($order->payment_status) }}</span></div>
                    @if($order->payment_method)
                        <div class="flex justify-between"><span class="text-gray-600">Payment Method</span><span class="font-medium">{{ $order->payment_method }}</span></div>
                    @endif
                    @if($order->paid_at)
                        <div class="flex justify-between"><span class="text-gray-600">Paid At</span><span class="font-medium">{{ $order->paid_at->format('d M Y H:i') }}</span></div>
                    @endif
                    @if($order->invoice_no)
                        <div class="flex justify-between"><span class="text-gray-600">Invoice No</span><span class="font-medium">{{ $order->invoice_no }}</span></div>
                    @endif
                    @if($order->external_id)
                        <div class="flex justify-between"><span class="text-gray-600">External ID</span><span class="font-medium">{{ $order->external_id }}</span></div>
                    @endif
                    @if($order->xendit_invoice_id)
                        <div class="flex justify-between"><span class="text-gray-600">Xendit Invoice ID</span><span class="font-mono text-xs">{{ $order->xendit_invoice_id }}</span></div>
                    @endif
                    @if($order->xendit_invoice_url)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Xendit URL</span>
                            <a class="text-blue-600 hover:text-blue-800" href="{{ $order->xendit_invoice_url }}" target="_blank" rel="noopener">Open</a>
                        </div>
                    @endif
                    @if($order->invoice)
                        <div class="flex justify-between"><span class="text-gray-600">Invoice Amount</span><span class="font-medium">Rp {{ number_format((float)($order->invoice->amount ?? 0), 0, ',', '.') }}</span></div>
                        @if($order->invoice->status)
                            <div class="flex justify-between"><span class="text-gray-600">Invoice Status</span><span class="font-medium">{{ ucfirst($order->invoice->status) }}</span></div>
                        @endif
                    @endif
                </div>
                <div class="mt-3 flex gap-2">
                    @if($order->xendit_invoice_url)
                        <button onclick="navigator.clipboard.writeText('{{ $order->xendit_invoice_url }}')" class="px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 rounded">Copy Pay Link</button>
                        <a href="{{ $order->xendit_invoice_url }}" target="_blank" class="px-3 py-2 text-xs bg-blue-600 text-white rounded">Open in Xendit</a>
                    @endif
                </div>
            </div>

            <!-- Shipping -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Shipping</h2>
                <div class="space-y-2 text-sm">
                    @if($order->shipping_method)
                        <div class="flex justify-between"><span class="text-gray-600">Method</span><span class="font-medium">{{ $order->shipping_method }}</span></div>
                    @endif
                    @if($order->resi)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Tracking (Resi)</span>
                            <span class="font-mono text-xs">{{ $order->resi }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Documents</h2>
                <div class="space-y-2 text-sm">
                    @if($order->invoice_pdf_url)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Invoice PDF</span>
                            <a href="{{ $order->invoice_pdf_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">View</a>
                        </div>
                    @endif
                    @if($order->receipt_pdf_url)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Receipt PDF</span>
                            <a href="{{ $order->receipt_pdf_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">View</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- History Timeline -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">History</h2>
                @if($order->histories && $order->histories->count())
                    <ol class="relative border-l border-gray-200 ml-2">
                        @foreach($order->histories as $h)
                        <li class="mb-6 ml-4">
                            <div class="absolute w-3 h-3 bg-primary-500 rounded-full -left-1.5 mt-1"></div>
                            <time class="mb-1 text-xs font-normal leading-none text-gray-400">{{ $h->created_at->format('d M Y H:i') }}</time>
                            <h3 class="text-sm font-semibold text-gray-900">{{ ucfirst($h->status) }}</h3>
                            @if($h->note)
                                <p class="text-sm text-gray-600">{{ $h->note }}</p>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-gray-500 text-sm">No history recorded.</p>
                @endif
            </div>

            <!-- Risk/Validation (simple flags) -->
            @php
                $mismatch = ($order->invoice && $order->invoice->amount) && (intval($order->invoice->amount) !== intval($order->total ?? $order->total_amount ?? 0));
            @endphp
            @if($mismatch)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r">
                <div class="text-sm text-yellow-800">
                    Possible price mismatch: Invoice Rp {{ number_format((float)($order->invoice->amount), 0, ',', '.') }} vs Order Rp {{ number_format((float)($order->total ?? $order->total_amount ?? 0), 0, ',', '.') }}
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
