@extends('admin.layout')

@section('title', 'Bulk Product Management')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Bulk Product Management</h1>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.bulk-products.import-template') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Download Template
                        </a>
                        <button onclick="document.getElementById('importModal').style.display='block'" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Import Products
                        </button>
                        <a href="{{ route('admin.bulk-products.export') }}" 
                           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Export Products
                        </a>
                    </div>
                </div>

                <!-- Bulk Action Form -->
                <form id="bulkActionForm" action="{{ route('admin.bulk-products.edit') }}" method="POST" class="mb-6">
                    @csrf
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Action</label>
                                <select name="action" id="bulkAction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Action</option>
                                    <option value="update_category">Update Category</option>
                                    <option value="update_status">Update Status</option>
                                    <option value="update_price">Update Price</option>
                                    <option value="delete">Delete Products</option>
                                </select>
                            </div>

                            <!-- Dynamic fields based on action -->
                            <div id="categoryField" class="hidden">
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="statusField" class="hidden">
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div id="priceFields" class="hidden col-span-2">
                                <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price Action</label>
                                    <select name="price_action" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="increase">Increase</option>
                                        <option value="decrease">Decrease</option>
                                        <option value="set">Set To</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select name="price_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Value</label>
                                    <input type="number" name="price_value" step="0.01" min="0" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                </div>
                            </div>

                            <div class="flex items-end">
                                <button type="submit" id="bulkActionBtn" disabled 
                                        class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-300 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Apply Action
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Products Table -->
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Variants</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" 
                                           class="product-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ $product->image_url ?? 'https://via.placeholder.com/40' }}" 
                                             alt="{{ $product->name }}">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $product->sku ?? 'No SKU' }}
                                                @if($product->variants_count > 0)
                                                    • {{ $product->variants_count }} variant{{ $product->variants_count > 1 ? 's' : '' }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ App\Helpers\CurrencyHelper::formatRupiah($product->price) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $product->total_stock > 10 ? 'bg-green-100 text-green-800' : 
                                           ($product->total_stock > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $product->total_stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($product->variants->isNotEmpty())
                                        <div class="space-y-1">
                                            @foreach($product->variants->take(3) as $variant)
                                                <div class="text-xs">
                                                    {{ $variant->name }}: {{ $variant->stock_quantity ?? 0 }}
                                                    @if($variant->price)
                                                        ({{ App\Helpers\CurrencyHelper::formatRupiah($variant->price) }})
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($product->variants->count() > 3)
                                                <div class="text-xs text-gray-500">+{{ $product->variants->count() - 3 }} more</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">No variants</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900">Import Products</h3>
            <div class="mt-2 px-7 py-3">
                <form action="{{ route('admin.bulk-products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.csv,.xls" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <div class="items-center px-4 py-3">
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700">
                            Import
                        </button>
                        <button type="button" onclick="document.getElementById('importModal').style.display='none'" 
                                class="mt-3 px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bulkAction = document.getElementById('bulkAction');
    const categoryField = document.getElementById('categoryField');
    const statusField = document.getElementById('statusField');
    const priceFields = document.getElementById('priceFields');
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    const selectAllCheckbox = document.getElementById('selectAll');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');

    // Handle bulk action selection
    bulkAction.addEventListener('change', function() {
        categoryField.classList.add('hidden');
        statusField.classList.add('hidden');
        priceFields.classList.add('hidden');

        switch(this.value) {
            case 'update_category':
                categoryField.classList.remove('hidden');
                break;
            case 'update_status':
                statusField.classList.remove('hidden');
                break;
            case 'update_price':
                priceFields.classList.remove('hidden');
                break;
        }
        
        updateBulkActionButton();
    });

    // Handle select all
    selectAllCheckbox.addEventListener('change', function() {
        productCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionButton();
    });

    // Handle individual checkboxes
    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionButton);
    });

    function updateBulkActionButton() {
        const selectedProducts = document.querySelectorAll('.product-checkbox:checked');
        const hasAction = bulkAction.value !== '';
        bulkActionBtn.disabled = !(selectedProducts.length > 0 && hasAction);
    }

    // Add selected product IDs to form before submission
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
        const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
        if (selectedCheckboxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one product');
            return;
        }

        if (!confirm('Are you sure you want to apply this action to ' + selectedCheckboxes.length + ' product(s)?')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
