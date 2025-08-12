@extends('admin.layout')

@section('title', 'Flash Sale Details')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Flash Sale Details</h1>
                <p class="text-gray-600 mt-1">View and manage flash sale information</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.flash-sales.edit', $flashSale) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 transition ease-in-out duration-150">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Flash Sale
                </a>
                <a href="{{ route('admin.flash-sales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Flash Sales
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Flash Sale Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">{{ $flashSale->title }}</h2>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $flashSale->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            {{ $flashSale->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        
                        @if($flashSale->starts_at <= now() && $flashSale->ends_at >= now() && $flashSale->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 animate-pulse">
                                <i class="fas fa-fire text-xs mr-1"></i>
                                LIVE NOW
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 p-4 rounded-lg border border-red-200">
                        <h3 class="text-sm font-semibold text-red-700 mb-1">Discount Percentage</h3>
                        <p class="text-3xl font-bold text-red-600">{{ $flashSale->discount_percentage }}%</p>
                    </div>
                    
                    @if($flashSale->max_discount_amount)
                        <div class="bg-gradient-to-r from-orange-50 to-yellow-50 p-4 rounded-lg border border-orange-200">
                            <h3 class="text-sm font-semibold text-orange-700 mb-1">Max Discount</h3>
                            <p class="text-xl font-bold text-orange-600">Rp {{ number_format($flashSale->max_discount_amount, 0, ',', '.') }}</p>
                        </div>
                    @endif
                    
                    @if($flashSale->max_quantity)
                        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-4 rounded-lg border border-purple-200">
                            <h3 class="text-sm font-semibold text-purple-700 mb-1">Max Quantity</h3>
                            <p class="text-xl font-bold text-purple-600">{{ $flashSale->max_quantity }} items</p>
                        </div>
                    @endif
                </div>
                
                @if($flashSale->description)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                        <p class="text-gray-700">{{ $flashSale->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Duration & Countdown -->
            <div class="bg-gradient-to-r from-red-500 to-pink-600 shadow-xl rounded-xl p-6 text-white">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-clock mr-2"></i>
                    Flash Sale Duration
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-semibold text-red-100 mb-1">Start Time</h4>
                        <p class="text-xl font-bold">{{ $flashSale->starts_at->format('M d, Y H:i') }}</p>
                        <p class="text-sm text-red-200">{{ $flashSale->starts_at->diffForHumans() }}</p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-semibold text-red-100 mb-1">End Time</h4>
                        <p class="text-xl font-bold">{{ $flashSale->ends_at->format('M d, Y H:i') }}</p>
                        <p class="text-sm text-red-200">{{ $flashSale->ends_at->diffForHumans() }}</p>
                    </div>
                </div>

                @if($flashSale->starts_at <= now() && $flashSale->ends_at >= now() && $flashSale->is_active)
                    <div class="bg-white bg-opacity-20 rounded-lg p-4">
                        <h4 class="text-lg font-semibold mb-2">Time Remaining</h4>
                        <div id="countdown" class="text-2xl font-bold"></div>
                    </div>
                @endif
            </div>

            <!-- Applied Products -->
            @if($flashSale->products->count() > 0)
                <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-box mr-2 text-primary-600"></i>
                        Flash Sale Products ({{ $flashSale->products->count() }})
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($flashSale->products as $product)
                            <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-center space-x-3 mb-3">
                                    @if($product->image_path)
                                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-lg object-cover">
                                    @else
                                        <div class="w-16 h-16 bg-gray-300 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-gray-500"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <span class="text-sm text-gray-500 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                            <span class="text-lg font-bold text-red-600">
                                                Rp {{ number_format($product->price * (1 - $flashSale->discount_percentage / 100), 0, ',', '.') }}
                                            </span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                            {{ $flashSale->discount_percentage }}% OFF
                                        </span>
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
                    <a href="{{ route('admin.flash-sales.edit', $flashSale) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-primary-600 hover:to-primary-700 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Flash Sale
                    </a>
                    
                    <form action="{{ route('admin.flash-sales.destroy', $flashSale) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this flash sale? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 transition ease-in-out duration-150">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Flash Sale
                        </button>
                    </form>
                </div>
            </div>

            <!-- Flash Sale Statistics -->
            <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-chart-bar mr-2 text-primary-600"></i>
                    Statistics
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Flash Sale ID</span>
                        <span class="text-sm font-semibold text-gray-900">#{{ $flashSale->id }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Products</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $flashSale->products->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Duration</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $flashSale->starts_at->diffInHours($flashSale->ends_at) }}h</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Created</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $flashSale->created_at->format('M d, Y') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Last Updated</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $flashSale->updated_at->format('M d, Y') }}</span>
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
                    @if($flashSale->is_active && $flashSale->starts_at <= now() && $flashSale->ends_at >= now())
                        <div class="mb-2">
                            <i class="fas fa-fire text-4xl text-red-500 animate-pulse"></i>
                        </div>
                        <p class="text-red-600 font-semibold">LIVE</p>
                        <p class="text-sm text-gray-600">Flash sale is currently active</p>
                    @elseif($flashSale->starts_at > now())
                        <div class="mb-2">
                            <i class="fas fa-clock text-4xl text-yellow-500"></i>
                        </div>
                        <p class="text-yellow-600 font-semibold">Scheduled</p>
                        <p class="text-sm text-gray-600">Will start {{ $flashSale->starts_at->diffForHumans() }}</p>
                    @elseif($flashSale->ends_at < now())
                        <div class="mb-2">
                            <i class="fas fa-times-circle text-4xl text-gray-500"></i>
                        </div>
                        <p class="text-gray-600 font-semibold">Ended</p>
                        <p class="text-sm text-gray-600">Ended {{ $flashSale->ends_at->diffForHumans() }}</p>
                    @else
                        <div class="mb-2">
                            <i class="fas fa-pause-circle text-4xl text-gray-500"></i>
                        </div>
                        <p class="text-gray-600 font-semibold">Inactive</p>
                        <p class="text-sm text-gray-600">Flash sale is not active</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($flashSale->starts_at <= now() && $flashSale->ends_at >= now() && $flashSale->is_active)
        <script>
            // Countdown Timer
            function updateCountdown() {
                const endTime = new Date('{{ $flashSale->ends_at->toISOString() }}').getTime();
                const now = new Date().getTime();
                const timeLeft = endTime - now;

                if (timeLeft > 0) {
                    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                    document.getElementById('countdown').innerHTML = 
                        `${days}d ${hours}h ${minutes}m ${seconds}s`;
                } else {
                    document.getElementById('countdown').innerHTML = 'EXPIRED';
                    clearInterval(countdownInterval);
                }
            }

            const countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown(); // Initial call
        </script>
    @endif
@endsection
