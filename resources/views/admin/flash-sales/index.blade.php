@extends('admin.layout')

@section('title', 'Flash Sale Management')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Flash Sale Management</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage time-limited offers and lightning deals</p>
                    </div>
                    <div class="flex space-x-3 mt-4 sm:mt-0">
                        <button id="bulkActionBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium hidden">
                            Bulk Actions
                        </button>
                        <a href="{{ route('admin.flash-sales.create') }}" 
                           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Create Flash Sale
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-md bg-red-500 text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Flash Sales</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $flashSales->total() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-md bg-green-500 text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Sales</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $activeFlashSalesCount ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-md bg-blue-500 text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Items Sold</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $totalItemsSold ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-md bg-yellow-500 text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Revenue Generated</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ \App\Helpers\CurrencyHelper::formatRupiah($totalRevenue ?? 0) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="filter-status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="expired">Expired</option>
                            <option value="sold_out">Sold Out</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="filter-timeframe" class="block text-sm font-medium text-gray-700 mb-2">Timeframe</label>
                        <select id="filter-timeframe" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="tomorrow">Tomorrow</option>
                            <option value="this_week">This Week</option>
                            <option value="next_week">Next Week</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" id="search" placeholder="Search product name..." 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="flex items-end">
                        <button id="clearFilters" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Sales Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="min-w-full">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($flashSales as $flashSale)
                                @php($product = $flashSale->products->first())
                                @php($now = now()) {{-- define once per row --}}
                                <tr class="hover:bg-gray-50" data-flash-sale-id="{{ $flashSale->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="flash-sale-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ $flashSale->id }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($product && $product->image_url)
                                                <div class="h-12 w-12 flex-shrink-0">
                                                    <img class="h-12 w-12 rounded object-cover" src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                                </div>
                                            @else
                                                <div class="h-12 w-12 flex-shrink-0 bg-gray-200 rounded flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product?->name ?? 'Product not found' }}</div>
                                                <div class="text-xs text-gray-500">{{ Str::limit($flashSale->title, 40) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($product)
                                            @php($discountAmount = $flashSale->calculateDiscount($product->price))
                                            @php($salePrice = $product->price - $discountAmount)
                                            <div class="text-sm text-gray-500"><span class="line-through">{{ \App\Helpers\CurrencyHelper::formatRupiah($product->price) }}</span></div>
                                            <div class="text-sm font-medium text-gray-900">{{ \App\Helpers\CurrencyHelper::formatRupiah($salePrice) }}</div>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">-{{ round(($discountAmount / max(1,$product->price))*100) }}%</span>
                                        @else
                                            <div class="text-sm font-medium text-gray-900">—</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php($total = $flashSale->max_quantity ?? 0)
                                        @php($sold = $flashSale->sold_quantity ?? 0)
                                        @php($perc = $total>0 ? ($sold/$total)*100 : 0)
                                        <div class="text-sm text-gray-900">{{ $sold }} / {{ $total ?: '∞' }}</div>
                                        @if($total)
                                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1"><div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100,$perc) }}%"></div></div>
                                            <div class="text-xs text-gray-500 mt-1">{{ number_format($perc,1) }}% sold</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $flashSale->starts_at->format('d M, H:i') }}</div>
                                        <div class="text-sm text-gray-500">{{ $flashSale->ends_at->format('d M, H:i') }}</div>
                                        @php($timeLeftHours = $flashSale->ends_at->diffInHours($now, false))
                                        @if($flashSale->starts_at <= $now && $flashSale->ends_at >= $now)
                                            <div class="text-xs text-orange-600 font-medium">{{ $timeLeftHours > 24 ? floor($timeLeftHours/24) . 'd ' . ($timeLeftHours % 24) . 'h' : $timeLeftHours . 'h' }} lagi</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php($soldOut = $flashSale->max_quantity && ($flashSale->sold_quantity ?? 0) >= $flashSale->max_quantity)
                                        @php($isActive = $flashSale->is_active && $flashSale->starts_at <= $now && $flashSale->ends_at >= $now && !$soldOut)
                                        @if($now < $flashSale->starts_at)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Scheduled</span>
                                        @elseif($now > $flashSale->ends_at)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                                        @elseif($soldOut)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Sold Out</span>
                                        @elseif($isActive)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php($revenue = ($flashSale->sold_quantity ?? 0) * ($product ? ($product->price - $flashSale->calculateDiscount($product->price)) : 0))
                                        <div class="text-sm font-medium text-gray-900">{{ \App\Helpers\CurrencyHelper::formatRupiah($revenue) }}</div>
                                        <div class="text-xs text-gray-500">Revenue</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.flash-sales.show', $flashSale) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                            <a href="{{ route('admin.flash-sales.edit', $flashSale) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <button onclick="toggleFlashSaleStatus({{ $flashSale->id }}, {{ $flashSale->is_active ? 'false' : 'true' }})" class="text-yellow-600 hover:text-yellow-900">{{ $flashSale->is_active ? 'Deactivate' : 'Activate' }}</button>
                                            <button onclick="deleteFlashSale({{ $flashSale->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">No flash sales found</h3>
                                            <p class="mt-1 text-sm text-gray-500">Get started by creating a time-limited offer.</p>
                                            <div class="mt-6">
                                                <a href="{{ route('admin.flash-sales.create') }}" 
                                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                    Create Flash Sale
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($flashSales->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6 rounded-lg shadow">
                {{ $flashSales->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Bulk Actions Modal -->
<div id="bulkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Actions</h3>
            <div class="space-y-3">
                <button onclick="bulkAction('activate')" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Activate Selected
                </button>
                <button onclick="bulkAction('deactivate')" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Deactivate Selected
                </button>
                <button onclick="bulkAction('delete')" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Delete Selected
                </button>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeBulkModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// CSRF helper
function csrfToken() { return document.querySelector('meta[name="csrf-token"]').getAttribute('content'); }

// Generic fetch wrapper
async function apiRequest(url, method = 'POST', data = null) {
    const opts = { method, headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } };
    if (data) {
        if (data instanceof FormData) {
            opts.body = data;
        } else {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(data);
        }
    }
    const res = await fetch(url, opts);
    if (!res.ok) {
        let msg = 'Request failed';
        try { const j = await res.json(); msg = j.message || JSON.stringify(j); } catch(e) { msg = res.status + ' ' + res.statusText; }
        throw new Error(msg);
    }
    // Try parse json
    try { return await res.json(); } catch { return {}; }
}

document.addEventListener('DOMContentLoaded', function() {
    // Checkbox handling
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.flash-sale-checkbox');
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        toggleBulkActions();
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });
    
    function toggleBulkActions() {
        const checkedCount = document.querySelectorAll('.flash-sale-checkbox:checked').length;
        if (checkedCount > 0) {
            bulkActionBtn.classList.remove('hidden');
        } else {
            bulkActionBtn.classList.add('hidden');
        }
        selectAll.checked = checkedCount === checkboxes.length;
    }
    
    // Bulk actions modal
    document.getElementById('bulkActionBtn').addEventListener('click', function() {
        document.getElementById('bulkModal').classList.remove('hidden');
    });
    
    // Filters
    const filterInputs = document.querySelectorAll('#filter-status, #filter-timeframe, #search');
    filterInputs.forEach(input => {
        input.addEventListener('input', applyFilters);
    });
    
    document.getElementById('clearFilters').addEventListener('click', function() {
        filterInputs.forEach(input => input.value = '');
        applyFilters();
    });
    
    function applyFilters() {
        const status = document.getElementById('filter-status').value.toLowerCase();
        const timeframe = document.getElementById('filter-timeframe').value.toLowerCase();
        const search = document.getElementById('search').value.toLowerCase();
        
        const rows = document.querySelectorAll('tbody tr[data-flash-sale-id]');
        rows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(6) span').textContent.toLowerCase();
            const productCell = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            
            const statusMatch = !status || statusCell.includes(status);
            const searchMatch = !search || productCell.includes(search);
            
            // Basic timeframe filtering (you'd want to implement this properly)
            let timeframeMatch = true;
            
            if (statusMatch && timeframeMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
});

async function toggleFlashSaleStatus(flashSaleId, newStatus) {
    try {
        const route = `{{ url('simple-admin/flash-sales') }}/${flashSaleId}/toggle-status`;
        await apiRequest(route, 'PATCH');
        location.reload();
    } catch (e) {
        alert('Failed to toggle status: ' + e.message);
    }
}

async function deleteFlashSale(flashSaleId) {
    if (!confirm('Are you sure you want to delete this flash sale?')) return;
    try {
        const route = `{{ url('simple-admin/flash-sales') }}/${flashSaleId}`;
        await apiRequest(route, 'DELETE');
        // Remove row without full reload
        const row = document.querySelector(`tr[data-flash-sale-id='${flashSaleId}']`);
        if (row) row.remove();
    } catch (e) {
        alert('Failed to delete: ' + e.message);
    }
}

async function bulkAction(action) {
    const selectedIds = Array.from(document.querySelectorAll('.flash-sale-checkbox:checked')).map(cb => cb.value);
    if (!selectedIds.length) { alert('Please select at least one flash sale'); return; }
    if (action === 'delete' && !confirm(`Delete ${selectedIds.length} flash sale(s)?`)) return;
    try {
        const route = `{{ route('admin.flash-sales.bulk-action') }}`;
        await apiRequest(route, 'POST', { action, flash_sale_ids: selectedIds });
        location.reload();
    } catch(e) {
        alert('Bulk action failed: ' + e.message);
    } finally { closeBulkModal(); }
}

function closeBulkModal() { document.getElementById('bulkModal').classList.add('hidden'); }
</script>
@endsection
