@extends('admin.layout')

@section('title', 'Invoice Details')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Invoice Details</h1>
                <p class="text-gray-600 mt-1">View and manage invoice information</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.invoices.edit', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 transition ease-in-out duration-150">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Invoice
                </a>
                <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Invoices
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Invoice Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Invoice Details -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Invoice #{{ $invoice->id }}</h2>
                        <p class="text-gray-600">External ID: {{ $invoice->external_id }}</p>
                    </div>
                    @php
                        $statusColors = [
                            'PENDING' => 'bg-yellow-100 text-yellow-800',
                            'PAID' => 'bg-green-100 text-green-800',
                            'SETTLED' => 'bg-blue-100 text-blue-800',
                            'EXPIRED' => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $invoice->status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Amount</h3>
                            <p class="text-2xl font-bold text-primary-600">
                                @if($invoice->currency === 'IDR')
                                    Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                @else
                                    ${{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}
                                @endif
                            </p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">User</h3>
                            <p class="text-lg font-medium text-gray-900">{{ $invoice->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $invoice->user->email }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @if($invoice->order)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-1">Linked Order</h3>
                                <a href="{{ route('admin.orders.show', $invoice->order) }}" class="text-lg font-medium text-primary-600 hover:text-primary-700">
                                    Order #{{ $invoice->order->id }}
                                </a>
                                <p class="text-sm text-gray-600">Rp {{ number_format($invoice->order->total_amount, 0, ',', '.') }}</p>
                            </div>
                        @endif

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Expiry Date</h3>
                            <p class="text-lg font-medium text-gray-900">
                                {{ $invoice->expiry_date ? $invoice->expiry_date->format('M d, Y H:i') : 'No expiry' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($invoice->description)
                <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-align-left mr-2 text-primary-600"></i>
                        Description
                    </h3>
                    <div class="prose prose-gray max-w-none">
                        <p class="text-gray-700 leading-relaxed">{{ $invoice->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Payment Details -->
            @if($invoice->payer_email || $invoice->invoice_url)
                <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-credit-card mr-2 text-primary-600"></i>
                        Payment Details
                    </h3>
                    <div class="space-y-3">
                        @if($invoice->payer_email)
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Payer Email:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $invoice->payer_email }}</span>
                            </div>
                        @endif
                        @if($invoice->invoice_url)
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Payment URL:</span>
                                <a href="{{ $invoice->invoice_url }}" target="_blank" class="text-sm text-primary-600 hover:text-primary-700 ml-2">
                                    Open Payment Link <i class="fas fa-external-link-alt ml-1"></i>
                                </a>
                            </div>
                        @endif
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
                    <a href="{{ route('admin.invoices.edit', $invoice) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Invoice
                    </a>
                    
                    @if($invoice->status !== 'PAID')
                        <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this invoice? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 transition ease-in-out duration-150">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Invoice
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle mr-2 text-primary-600"></i>
                    Invoice Information
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Invoice ID</span>
                        <span class="text-sm font-semibold text-gray-900">#{{ $invoice->id }}</span>
                    </div>
                    
                    @if($invoice->xendit_id)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Xendit ID</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $invoice->xendit_id }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Created</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $invoice->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Last Updated</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $invoice->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Currency</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $invoice->currency }}</span>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-chart-line mr-2 text-primary-600"></i>
                    Status Information
                </h3>
                <div class="text-center">
                    @if($invoice->status === 'PAID')
                        <div class="mb-2">
                            <i class="fas fa-check-circle text-4xl text-green-500"></i>
                        </div>
                        <p class="text-green-600 font-semibold">Paid</p>
                        <p class="text-sm text-gray-600">Invoice has been paid successfully</p>
                    @elseif($invoice->status === 'PENDING')
                        <div class="mb-2">
                            <i class="fas fa-clock text-4xl text-yellow-500"></i>
                        </div>
                        <p class="text-yellow-600 font-semibold">Pending Payment</p>
                        <p class="text-sm text-gray-600">Waiting for payment</p>
                    @elseif($invoice->status === 'EXPIRED')
                        <div class="mb-2">
                            <i class="fas fa-times-circle text-4xl text-red-500"></i>
                        </div>
                        <p class="text-red-600 font-semibold">Expired</p>
                        <p class="text-sm text-gray-600">Invoice has expired</p>
                    @else
                        <div class="mb-2">
                            <i class="fas fa-info-circle text-4xl text-blue-500"></i>
                        </div>
                        <p class="text-blue-600 font-semibold">{{ $invoice->status }}</p>
                        <p class="text-sm text-gray-600">Current invoice status</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
