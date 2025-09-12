<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\BulkProductController;
use App\Http\Controllers\Admin\InventoryController;
// use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\FavoriteController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\XenditRedirectController;

Route::get('/', function () {
    return view('welcome');
});

// Xendit payment redirects (used by success_redirect_url / failure_redirect_url)
Route::get('/payments/xendit/redirect/success', [XenditRedirectController::class, 'success'])->name('xendit.redirect.success');
Route::get('/payments/xendit/redirect/failure', [XenditRedirectController::class, 'failure'])->name('xendit.redirect.failure');

// Admin routes - protected with basic auth & admin
Route::middleware(['web','auth.basic','admin'])->prefix('simple-admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Products
    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/toggle-discount', [ProductController::class, 'toggleDiscount'])->name('products.toggle-discount');
    
    // Categories
    Route::resource('categories', CategoryController::class);
    
    // Users
    Route::resource('users', UserController::class);
    
    // Orders
    Route::resource('orders', OrderController::class);
    
    // Discounts (disabled)
    // Route::resource('discounts', DiscountController::class);
    // Route::patch('discounts/{discount}/toggle-status', [DiscountController::class, 'toggleStatus'])->name('discounts.toggle-status');
    // Route::post('discounts/bulk-action', [DiscountController::class, 'bulkAction'])->name('discounts.bulk-action');
    
    // Flash Sales (disabled)
    // Route::resource('flash-sales', FlashSaleController::class);
    // Route::patch('flash-sales/{flash_sale}/toggle-status', [FlashSaleController::class, 'toggleStatus'])->name('flash-sales.toggle-status');
    // Route::post('flash-sales/bulk-action', [FlashSaleController::class, 'bulkAction'])->name('flash-sales.bulk-action');
    // Route::get('flash-sales/{flash_sale}/analytics', [FlashSaleController::class, 'analytics'])->name('flash-sales.analytics');

    // Bulk Product Management
    Route::get('bulk-products', [BulkProductController::class, 'index'])->name('bulk-products.index');
    Route::post('bulk-products/edit', [BulkProductController::class, 'bulkEdit'])->name('bulk-products.edit');
    Route::get('bulk-products/export', [BulkProductController::class, 'export'])->name('bulk-products.export');
    Route::get('bulk-products/import-template', [BulkProductController::class, 'importTemplate'])->name('bulk-products.import-template');
    Route::post('bulk-products/import', [BulkProductController::class, 'import'])->name('bulk-products.import');

    // Inventory Management
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/alerts', [InventoryController::class, 'alerts'])->name('inventory.alerts');
    Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('inventory/reports', [InventoryController::class, 'reports'])->name('inventory.reports');
    Route::post('inventory/products/{product}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
    Route::post('inventory/bulk-update', [InventoryController::class, 'bulkUpdate'])->name('inventory.bulk-update');
    Route::get('inventory/export', [InventoryController::class, 'export'])->name('inventory.export');

    // Product Variants
    // Route::resource('variants', VariantController::class);

    // Favorites Management
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::get('favorites-analytics', [FavoriteController::class, 'analytics'])->name('favorites.analytics');
    Route::get('favorites/{favorite}', [FavoriteController::class, 'show'])->name('favorites.show');
    Route::delete('favorites/{favorite}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    
    // Cart Management
    Route::get('carts', [CartController::class, 'index'])->name('carts.index');
    Route::get('carts-abandoned', [CartController::class, 'abandoned'])->name('carts.abandoned');
    Route::get('carts/{cart}', [CartController::class, 'show'])->name('carts.show');
    Route::delete('carts/{cart}', [CartController::class, 'destroy'])->name('carts.destroy');
    Route::post('carts/{cart}/recovery-email', [CartController::class, 'sendRecoveryEmail'])->name('carts.recovery-email');
    Route::delete('cart-items/{cartProduct}', [CartController::class, 'removeItem'])->name('carts.remove-item');

    // Campaign Management
    Route::resource('campaigns', CampaignController::class);
    Route::get('campaigns-analytics', [CampaignController::class, 'analytics'])->name('campaigns.analytics');
    Route::post('campaigns-bulk-action', [CampaignController::class, 'bulkAction'])->name('campaigns.bulk-action');
    Route::post('campaigns/{campaign}/activate', [CampaignController::class, 'activate'])->name('campaigns.activate');
    Route::post('campaigns/{campaign}/pause', [CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('campaigns/{campaign}/resume', [CampaignController::class, 'resume'])->name('campaigns.resume');
    Route::post('campaigns/{campaign}/complete', [CampaignController::class, 'complete'])->name('campaigns.complete');

    // Support Management
    Route::resource('support', SupportController::class);
    Route::post('support-bulk-action', [SupportController::class, 'bulkAction'])->name('support.bulk-action');
    Route::post('support/{ticket}/assign', [SupportController::class, 'assign'])->name('support.assign');
    Route::post('support/{ticket}/status', [SupportController::class, 'changeStatus'])->name('support.change-status');
    Route::post('support/{ticket}/message', [SupportController::class, 'addMessage'])->name('support.add-message');

    // Invoice Management
    Route::resource('invoices', InvoiceController::class);

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
    Route::get('analytics/revenue', [AnalyticsController::class, 'revenue'])->name('analytics.revenue');
    Route::get('analytics/products', [AnalyticsController::class, 'products'])->name('analytics.products');
    Route::get('analytics/customers', [AnalyticsController::class, 'customers'])->name('analytics.customers');
    Route::get('analytics/sales-trends', [AnalyticsController::class, 'salesTrends'])->name('analytics.sales-trends');
    Route::get('analytics/category-performance', [AnalyticsController::class, 'categoryPerformance'])->name('analytics.category-performance');
    Route::get('analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');

    // Review Management
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews-analytics', [ReviewController::class, 'analytics'])->name('reviews.analytics');
    Route::post('reviews/bulk-action', [ReviewController::class, 'bulkAction'])->name('reviews.bulk-action');
    Route::get('reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
    Route::post('reviews/{review}/respond', [ReviewController::class, 'respond'])->name('reviews.respond');
});

// Debug route to test if Laravel is working
Route::get('/test', function () {
    return response()->json([
        'status' => 'Laravel is working',
        'time' => now(),
        'session_driver' => config('session.driver'),
        'app_debug' => config('app.debug'),
    ]);
});

// Debug admin access
Route::get('/debug-admin', function () {
    try {
        return response()->json([
            'status' => 'Admin routes working',
            'dashboard_url' => route('admin.dashboard'),
            'time' => now(),
        ]);
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});