@extends('admin.layout')

@section('title', 'Inventory Reports')

@section('content')
<!-- Include required assets -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    /* Modern Export Button Animation */
    @keyframes shimmer {
        0% { background-position: -200px 0; }
        100% { background-position: calc(200px + 100%) 0; }
    }
    
    .export-shimmer {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        background-size: 200px 100%;
        animation: shimmer 2s infinite;
    }
</style>

<!-- Spinner & Toast Styles -->
<style>
    .nf-spinner-overlay { position: fixed; inset:0; background: rgba(255,255,255,0.7); display:none; align-items:center; justify-content:center; z-index:50; }
    .nf-spinner { border:4px solid #e5e7eb; border-top:4px solid #6366f1; border-radius:50%; width:52px; height:52px; animation:spin 0.9s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg);} }
    .nf-toast-wrapper { position: fixed; top:1rem; right:1rem; z-index:60; display:flex; flex-direction:column; gap:.5rem; }
    .nf-toast { background:#111827; color:#fff; padding:.75rem 1rem; border-radius:.5rem; font-size:.875rem; box-shadow:0 4px 12px rgba(0,0,0,.15); display:flex; align-items:center; gap:.5rem; }
    .nf-toast-success { background:#065f46; }
    .nf-toast-warn { background:#92400e; }
    .nf-toast-info { background:#1e3a8a; }
</style>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Simplified header (removed purple banner) -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-box-open text-indigo-500 mr-2"></i>
                Inventory Reports & Export
            </h1>
            <p class="text-sm text-gray-500 mt-1">Laporan ringkas stok & export Excel multi-sheet.</p>
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
                    
                    <!-- Single Modern Export Button -->
                    <button type="button" id="modernExportBtn"
                            class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-lg opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                        <i class="fas fa-file-excel text-xl mr-3 animate-pulse group-hover:animate-none"></i>
                        <div class="flex flex-col items-start relative z-10">
                            <span class="text-base font-bold">Export Excel</span>
                            <span class="text-xs text-emerald-100 font-normal">Modern • Professional • Beautiful</span>
                        </div>
                        <i class="fas fa-download ml-3 text-lg group-hover:translate-y-0.5 transition-transform duration-300"></i>
                    </button>
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
    <!-- Spinner Overlay -->
    <div id="nfSpinner" class="nf-spinner-overlay"><div class="nf-spinner"></div></div>
    <!-- Toast Container -->
    <div id="nfToastWrapper" class="nf-toast-wrapper"></div>

    <script>
        function nfShowSpinner(show){document.getElementById('nfSpinner').style.display=show?'flex':'none'}
        function nfToast(msg,type='info',timeout=4500){
            const wrap=document.getElementById('nfToastWrapper');
            const el=document.createElement('div');
            el.className='nf-toast '+(type==='success'?'nf-toast-success':type==='warn'?'nf-toast-warn':'nf-toast-info');
            el.innerHTML='<span>'+msg+'</span>';
            wrap.appendChild(el);
            setTimeout(()=>{el.style.opacity='0';setTimeout(()=>el.remove(),400)},timeout);
        }
        
        // Modern Excel export function
        function exportModernExcel(url){
            nfShowSpinner(true);
            nfToast('Generating beautiful Excel report...', 'info', 2000);
            
            // Start download
            window.location = url;
            
            // Hide spinner and show success after delay
            setTimeout(() => { 
                nfShowSpinner(false); 
                nfToast('✨ Modern Excel export completed! Check your downloads.', 'success', 5000); 
            }, 2500);
        }
        document.addEventListener('DOMContentLoaded',()=>{
            const modernExportUrl = '{{ route('admin.inventory.export-reports') }}';
            const btn = document.getElementById('modernExportBtn');
            
            btn.addEventListener('click', function() {
                // Add shimmer effect
                this.classList.add('export-shimmer');
                
                // Visual feedback
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                    this.classList.remove('export-shimmer');
                }, 300);
                
                // Start the modern export
                exportModernExcel(modernExportUrl);
            });
        });
    </script>
@endsection
