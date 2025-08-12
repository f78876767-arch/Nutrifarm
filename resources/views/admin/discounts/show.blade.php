@extends('admin.layout')

@section('title', 'Discount Details')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Discount Details</h1>
                <p class="text-gray-600 mt-1">View and manage discount information</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.discounts.edit', $discount) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 transition ease-in-out duration-150">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Discount
                </a>
                <a href="{{ route('admin.discounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Discounts
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Discount Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $discount->name }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $discount->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas fa-circle text-xs mr-1"></i>
                                {{ $discount->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-tag text-xs mr-1"></i>
                                {{ strtoupper(str_replace('_', ' ', $discount->type)) }}
                            </span>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Discount Value</h3>
                            <p class="text-2xl font-bold text-primary-600">
                                @if($discount->type === 'percentage')
                                    {{ $discount->value }}%
                                @elseif($discount->type === 'fixed_amount')
                                    Rp {{ number_format($discount->value, 0, ',', '.') }}
                                @else
                                    Buy {{ $discount->value }} Get {{ $discount->get_quantity ?? 1 }}
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        @if($discount->min_purchase_amount)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-1">Minimum Purchase</h3>
                                <p class="text-lg font-bold text-gray-900">Rp {{ number_format($discount->min_purchase_amount, 0, ',', '.') }}</p>
                            </div>
                        @endif
                        
                        @if($discount->max_discount_amount)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-1">Maximum Discount</h3>
                                <p class="text-lg font-bold text-gray-900">Rp {{ number_format($discount->max_discount_amount, 0, ',', '.') }}</p>
                            </div>
                        @endif
                        
                        @if($discount->usage_limit)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-sm font-semibold text-gray-700 mb-1">Usage Limit</h3>
                                <p class="text-lg font-bold text-gray-900">{{ $discount->usage_limit }} times</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                @if($discount->description)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                        <p class="text-gray-700">{{ $discount->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Duration -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                    Duration
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">Start Date</h4>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $discount->starts_at ? $discount->starts_at->format('M d, Y H:i') : 'No start date' }}
                        </p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">End Date</h4>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $discount->ends_at ? $discount->ends_at->format('M d, Y H:i') : 'No end date' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Applied Products -->
            @if($discount->products->count() > 0)
                <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-box mr-2 text-primary-600"></i>
                        Applied Products ({{ $discount->products->count() }})
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($discount->products as $product)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center space-x-3">
                                    @if($product->image_path)
                                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-lg object-cover">
                                    @else
                                        <div class="w-12 h-12 bg-gray-300 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-gray-500"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 text-sm">{{ $product->name }}</h4>
                                        <p class="text-xs text-gray-500">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
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
                    <a href="{{ route('admin.discounts.edit', $discount) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Discount
                    </a>
                    
                    <form action="{{ route('admin.discounts.destroy', $discount) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this discount? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 transition ease-in-out duration-150">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Discount
                        </button>
                    </form>
                </div>
            </div>

            <!-- Discount Statistics -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-chart-bar mr-2 text-primary-600"></i>
                    Statistics
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Discount ID</span>
                        <span class="text-sm font-semibold text-gray-900">#{{ $discount->id }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Products Applied</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $discount->products->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Created</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $discount->created_at->format('M d, Y') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Last Updated</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $discount->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Status Indicator -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle mr-2 text-primary-600"></i>
                    Status
                </h3>
                <div class="text-center">
                    @if($discount->is_active && (!$discount->starts_at || $discount->starts_at <= now()) && (!$discount->ends_at || $discount->ends_at >= now()))
                        <div class="mb-2">
                            <i class="fas fa-check-circle text-4xl text-green-500"></i>
                        </div>
                        <p class="text-green-600 font-semibold">Active</p>
                        <p class="text-sm text-gray-600">Discount is currently active</p>
                    @elseif($discount->starts_at && $discount->starts_at > now())
                        <div class="mb-2">
                            <i class="fas fa-clock text-4xl text-yellow-500"></i>
                        </div>
                        <p class="text-yellow-600 font-semibold">Scheduled</p>
                        <p class="text-sm text-gray-600">Will start {{ $discount->starts_at->diffForHumans() }}</p>
                    @elseif($discount->ends_at && $discount->ends_at < now())
                        <div class="mb-2">
                            <i class="fas fa-times-circle text-4xl text-red-500"></i>
                        </div>
                        <p class="text-red-600 font-semibold">Expired</p>
                        <p class="text-sm text-gray-600">Ended {{ $discount->ends_at->diffForHumans() }}</p>
                    @else
                        <div class="mb-2">
                            <i class="fas fa-pause-circle text-4xl text-gray-500"></i>
                        </div>
                        <p class="text-gray-600 font-semibold">Inactive</p>
                        <p class="text-sm text-gray-600">Discount is not active</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
