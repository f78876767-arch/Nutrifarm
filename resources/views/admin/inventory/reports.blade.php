@extends('admin.layout')

@section('title', 'Inventory Reports')

@section('content')
<!-- Include required assets -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    .export-dropdown {
        min-width: 280px;
    }
    .export-item:hover {
        background-color: #f9fafb;
        transform: translateX(2px);
        transition: all 0.2s ease;
    }
    .export-category {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 600;
    }
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Export Info Banner -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2">📊 Inventory Reports & Export</h1>
                    <p class="text-indigo-100">Generate comprehensive inventory reports with professional Excel formatting</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-white bg-opacity-20 rounded-lg p-4">
                        <div class="text-center">
                            <i class="fas fa-file-excel text-3xl mb-2"></i>
                            <div class="text-sm font-medium">Multi-Sheet<br>Excel Report</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Export Preview -->
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div class="bg-white bg-opacity-15 rounded-lg p-3 text-center border border-white border-opacity-20">
                    <i class="fas fa-chart-pie text-lg mb-2"></i>
                    <div class="font-medium">Summary Sheet</div>
                    <div class="text-xs text-indigo-200 mt-1">Overview & stats</div>
                </div>
                <div class="bg-white bg-opacity-15 rounded-lg p-3 text-center border border-white border-opacity-20">
                    <i class="fas fa-boxes text-lg mb-2"></i>
                    <div class="font-medium">Products Sheet</div>
                    <div class="text-xs text-indigo-200 mt-1">Detailed product list</div>
                </div>
                <div class="bg-white bg-opacity-15 rounded-lg p-3 text-center border border-white border-opacity-20">
                    <i class="fas fa-tags text-lg mb-2"></i>
                    <div class="font-medium">Categories Sheet</div>
                    <div class="text-xs text-indigo-200 mt-1">Category breakdown</div>
                </div>
                <div class="bg-white bg-opacity-15 rounded-lg p-3 text-center border border-white border-opacity-20">
                    <i class="fas fa-exclamation-triangle text-lg mb-2"></i>
                    <div class="font-medium">Low Stock Sheet</div>
                    <div class="text-xs text-indigo-200 mt-1">Items need restock</div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Products</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalProducts) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15v-3a1 1 0 011-1h2a1 1 0 011 1v3H8z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Stock Units</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalStock) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.51-1.31c-.562-.649-1.413-1.076-2.353-1.253V5z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Stock Value</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ App\Helpers\CurrencyHelper::formatRupiah($stockValue) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Variants</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalVariants ?? 0) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15v-3a1 1 0 011-1h2a1 1 0 011 1v3H8z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Variant Stock</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalStock ?? 0) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-medium text-gray-900">Stock by Category</h2>
                    <div class="flex space-x-3">
                        <!-- Dropdown Export Button -->
                        <div class="relative inline-block text-left" x-data="{ open: false }">
                            <div>
                                <button type="button" @click="open = !open" 
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors duration-200">
                                    <i class="fas fa-download mr-2"></i>
                                    Export Options
                                    <i class="fas fa-chevron-down ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                </button>
                            </div>

                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" 
                                 class="export-dropdown origin-top-right absolute right-0 mt-2 rounded-lg shadow-xl bg-white ring-1 ring-gray-200 focus:outline-none z-20 border">
                                <div class="py-2">
                                    <!-- Header -->
                                    <div class="px-4 py-3 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-t-lg border-b">
                                        <h3 class="export-category text-sm font-semibold">📊 Professional Reports</h3>
                                        <p class="text-xs text-gray-600 mt-1">Comprehensive multi-sheet Excel reports</p>
                                    </div>
                                    
                                    <!-- Excel Detailed Report -->
                                    <a href="{{ route('admin.inventory.export-reports', ['mode' => 'full']) }}" 
                                       class="export-item group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 transition-all duration-200">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-file-excel text-green-500 text-lg mr-3"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">Complete Excel Report</div>
                                            <div class="text-xs text-gray-500 mt-1">4 sheets: Summary, Products, Categories, Low Stock</div>
                                            <div class="text-xs text-green-600 font-medium mt-1">✨ Recommended</div>
                                        </div>
                                    </a>
                                    
                                    <!-- Divider -->
                                    <div class="my-2 border-t border-gray-100"></div>
                                    <div class="px-4 py-2 bg-gray-50">
                                        <h4 class="text-xs font-semibold text-gray-700">📄 Basic Exports</h4>
                                        <p class="text-xs text-gray-500">Simple data formats</p>
                                    </div>
                                    
                                    <!-- CSV Export -->
                                    <a href="{{ route('admin.inventory.export', ['format' => 'csv']) }}" 
                                       class="export-item group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 transition-all duration-200">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-file-csv text-blue-500 text-lg mr-3"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">CSV Export</div>
                                            <div class="text-xs text-gray-500 mt-1">Basic product data for spreadsheets</div>
                                        </div>
                                    </a>
                                    
                                    <!-- Simple Excel -->
                                    <a href="{{ route('admin.inventory.export', ['format' => 'excel']) }}" 
                                       class="export-item group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 transition-all duration-200 rounded-b-lg">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-table text-emerald-600 text-lg mr-3"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">Simple Excel</div>
                                            <div class="text-xs text-gray-500 mt-1">Single sheet product list</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Excel Export Button -->
                        <a href="{{ route('admin.inventory.export-reports') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md shadow-sm">
                            <i class="fas fa-file-excel mr-2"></i>
                            Quick Excel Export
                        </a>
                    </div>
                </div>

                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Products</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Variants</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Total Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Stock Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Avg. Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($categoryStock as $category)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $category['name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($category['products_count']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($category['variants_count'] ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($category['total_stock']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ App\Helpers\CurrencyHelper::formatRupiah($category['stock_value']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($category['products_count'] > 0)
                                        {{ App\Helpers\CurrencyHelper::formatRupiah($category['stock_value'] / $category['products_count']) }}
                                    @else
                                        {{ App\Helpers\CurrencyHelper::formatRupiah(0) }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Total
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($totalProducts) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($totalVariants ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($totalStock) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ App\Helpers\CurrencyHelper::formatRupiah($stockValue) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500">
                                    @if($totalProducts > 0)
                                        {{ App\Helpers\CurrencyHelper::formatRupiah($stockValue / $totalProducts) }}
                                    @else
                                        {{ App\Helpers\CurrencyHelper::formatRupiah(0) }}
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Back to Inventory -->
        <div class="text-center">
            <a href="{{ route('admin.inventory.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                Back to Inventory Management
            </a>
        </div>
    </div>
</div>
@endsection
