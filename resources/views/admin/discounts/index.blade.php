@extends('admin.layout')

@section('title', 'Discount Management')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Discount Management</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage discount codes, promotions, and special offers</p>
                    </div>
                    <div class="flex space-x-3 mt-4 sm:mt-0">
                        <button id="bulkActionBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium hidden">
                            Bulk Actions
                        </button>
                        <a href="{{ route('admin.discounts.create') }}" 
                           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Create Discount
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
                            <div class="flex items-center justify-center h-8 w-8 rounded-md bg-indigo-500 text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Discounts</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $discounts->total() }}</dd>
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
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Discounts</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $activeDiscountsCount ?? 0 }}</dd>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Uses</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $totalUses ?? 0 }}</dd>
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
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Savings</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($totalSavings ?? 0, 2) }}</dd>
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
                            <option value="inactive">Inactive</option>
                            <option value="expired">Expired</option>
                            <option value="used_up">Used Up</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="filter-type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select id="filter-type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            <option value="percentage">Percentage</option>
                            <option value="fixed_amount">Fixed Amount</option>
                            <option value="buy_x_get_y">Buy X Get Y</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" id="search" placeholder="Search discount name or code..." 
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

        <!-- Discounts Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="min-w-full">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type & Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conditions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage Stats</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($discounts as $discount)
                                <tr class="hover:bg-gray-50" data-discount-id="{{ $discount->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="discount-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ $discount->id }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $discount->name ?? $discount->code }}</div>
                                                <div class="text-sm text-gray-500">Code: {{ $discount->code }}</div>
                                                @if($discount->description)
                                                    <div class="text-xs text-gray-400 mt-1">{{ Str::limit($discount->description, 40) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $discount->type === 'percentage' ? 'bg-blue-100 text-blue-800' : ($discount->type === 'fixed_amount' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                                {{ ucfirst(str_replace('_', ' ', $discount->type)) }}
                                            </span>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 mt-1">
                                            @if($discount->type === 'percentage')
                                                {{ $discount->value }}%
                                                @if($discount->max_discount_amount)
                                                    <span class="text-xs text-gray-500">(max ${{ number_format($discount->max_discount_amount, 2) }})</span>
                                                @endif
                                            @elseif($discount->type === 'fixed_amount')
                                                ${{ number_format($discount->value, 2) }}
                                            @else
                                                Buy {{ $discount->value_buy }} Get {{ $discount->get_quantity }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($discount->min_purchase_amount)
                                            <div>Min: ${{ number_format($discount->min_purchase_amount, 2) }}</div>
                                        @endif
                                        @if($discount->usage_limit)
                                            <div>Limit: {{ $discount->usage_limit }} uses</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $discount->used_count ?? 0 }}
                                            @if($discount->usage_limit)
                                                / {{ $discount->usage_limit }}
                                            @else
                                                / ∞
                                            @endif
                                        </div>
                                        @if($discount->usage_limit && $discount->used_count)
                                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($discount->used_count / $discount->usage_limit) * 100) }}%"></div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($discount->starts_at && $discount->ends_at)
                                            <div>{{ $discount->starts_at->format('M d') }} - {{ $discount->ends_at->format('M d, Y') }}</div>
                                        @elseif($discount->starts_at)
                                            <div>From {{ $discount->starts_at->format('M d, Y') }}</div>
                                        @elseif($discount->ends_at)
                                            <div>Until {{ $discount->ends_at->format('M d, Y') }}</div>
                                        @else
                                            <div>No expiration</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $now = now();
                                            $isActive = $discount->is_active && 
                                                       (!$discount->starts_at || $discount->starts_at <= $now) && 
                                                       (!$discount->ends_at || $discount->ends_at >= $now) &&
                                                       (!$discount->usage_limit || ($discount->used_count ?? 0) < $discount->usage_limit);
                                            $isExpired = $discount->ends_at && $discount->ends_at < $now;
                                            $isUsedUp = $discount->usage_limit && ($discount->used_count ?? 0) >= $discount->usage_limit;
                                        @endphp
                                        
                                        @if($isActive)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @elseif($isExpired)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                                        @elseif($isUsedUp)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Used Up</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.discounts.show', $discount) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                            <a href="{{ route('admin.discounts.edit', $discount) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <button onclick="toggleDiscountStatus({{ $discount->id }}, {{ $discount->is_active ? 'false' : 'true' }})" 
                                                    class="text-yellow-600 hover:text-yellow-900">
                                                {{ $discount->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button onclick="deleteDiscount({{ $discount->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">No discounts found</h3>
                                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new discount code.</p>
                                            <div class="mt-6">
                                                <a href="{{ route('admin.discounts.create') }}" 
                                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                    Create Discount
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
        @if($discounts->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6 rounded-lg shadow">
                {{ $discounts->links() }}
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
document.addEventListener('DOMContentLoaded', function() {
    // Checkbox handling
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.discount-checkbox');
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
        const checkedCount = document.querySelectorAll('.discount-checkbox:checked').length;
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
    const filterInputs = document.querySelectorAll('#filter-status, #filter-type, #search');
    filterInputs.forEach(input => {
        input.addEventListener('input', applyFilters);
    });
    
    document.getElementById('clearFilters').addEventListener('click', function() {
        filterInputs.forEach(input => input.value = '');
        applyFilters();
    });
    
    function applyFilters() {
        const status = document.getElementById('filter-status').value.toLowerCase();
        const type = document.getElementById('filter-type').value.toLowerCase();
        const search = document.getElementById('search').value.toLowerCase();
        
        const rows = document.querySelectorAll('tbody tr[data-discount-id]');
        rows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(7) span').textContent.toLowerCase();
            const typeCell = row.querySelector('td:nth-child(3) span').textContent.toLowerCase();
            const nameCell = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            
            const statusMatch = !status || statusCell.includes(status);
            const typeMatch = !type || typeCell.includes(type);
            const searchMatch = !search || nameCell.includes(search);
            
            if (statusMatch && typeMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
});

function closeBulkModal() {
    document.getElementById('bulkModal').classList.add('hidden');
}

function bulkAction(action) {
    const selectedIds = Array.from(document.querySelectorAll('.discount-checkbox:checked'))
                             .map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one discount');
        return;
    }
    
    if (action === 'delete' && !confirm(`Are you sure you want to delete ${selectedIds.length} discount(s)?`)) {
        return;
    }
    
    // Here you would make an AJAX request to perform the bulk action
    console.log(`Bulk ${action} for discounts:`, selectedIds);
    alert(`Bulk ${action} completed for ${selectedIds.length} discount(s)`);
    closeBulkModal();
    location.reload(); // Refresh the page
}

function toggleDiscountStatus(discountId, newStatus) {
    // Here you would make an AJAX request to toggle the discount status
    console.log(`Toggle discount ${discountId} to ${newStatus}`);
    alert(`Discount status updated`);
    location.reload(); // Refresh the page
}

function deleteDiscount(discountId) {
    if (confirm('Are you sure you want to delete this discount?')) {
        // Here you would make an AJAX request to delete the discount
        console.log(`Delete discount ${discountId}`);
        alert('Discount deleted');
        location.reload(); // Refresh the page
    }
}
</script>
@endsection
