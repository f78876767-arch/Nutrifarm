@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Products</p>
                    <p class="text-3xl font-bold">{{ $stats['products'] }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-50 rounded-full p-3">
                    <i class="fas fa-box text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm">Categories</p>
                    <p class="text-3xl font-bold">{{ $stats['categories'] }}</p>
                </div>
                <div class="bg-emerald-400 bg-opacity-50 rounded-full p-3">
                    <i class="fas fa-tags text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Total Users</p>
                    <p class="text-3xl font-bold">{{ $stats['users'] }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-50 rounded-full p-3">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Total Orders</p>
                    <p class="text-3xl font-bold">{{ $stats['orders'] }}</p>
                </div>
                <div class="bg-orange-400 bg-opacity-50 rounded-full p-3">
                    <i class="fas fa-shopping-cart text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Active Discounts</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['discounts'] }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-percent text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Revenue Today</p>
                    <p class="text-2xl font-bold text-gray-800">{{ App\Helpers\CurrencyHelper::formatRupiah($stats['revenue_today'] ?? 0) }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-bolt text-primary-500 mr-2"></i>
                Quick Actions
            </h3>
            <div class="space-y-3">
                <a href="{{ route('admin.products.create') }}" class="flex items-center p-3 rounded-lg bg-primary-50 hover:bg-primary-100 transition-colors group">
                    <i class="fas fa-plus text-primary-500 mr-3"></i>
                    <span class="text-gray-700 group-hover:text-primary-700">Add New Product</span>
                </a>
                <a href="{{ route('admin.categories.create') }}" class="flex items-center p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors group">
                    <i class="fas fa-plus text-blue-500 mr-3"></i>
                    <span class="text-gray-700 group-hover:text-blue-700">Add New Category</span>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-clock text-gray-500 mr-2"></i>
                System Status
            </h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <div>
                            <p class="font-medium text-gray-800">Database Connected</p>
                            <p class="text-sm text-gray-600">All systems operational</p>
                        </div>
                    </div>
                    <i class="fas fa-check-circle text-green-500"></i>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                        <div>
                            <p class="font-medium text-gray-800">Storage Available</p>
                            <p class="text-sm text-gray-600">Plenty of space for uploads</p>
                        </div>
                    </div>
                    <i class="fas fa-hdd text-blue-500"></i>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                        <div>
                            <p class="font-medium text-gray-800">Admin Panel Active</p>
                            <p class="text-sm text-gray-600">Last updated: {{ now()->format('g:i A') }}</p>
                        </div>
                    </div>
                    <i class="fas fa-shield-alt text-purple-500"></i>
                </div>
            </div>
        </div>
    </div>
@endsection
