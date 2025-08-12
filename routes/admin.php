<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController;

// Admin routes - require authentication
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Products
    Route::resource('products', ProductController::class);
    
    // We can add more resources here as we build them
    // Route::resource('categories', CategoryController::class);
    // Route::resource('users', UserController::class);
    // Route::resource('orders', OrderController::class);
    // Route::resource('discounts', DiscountController::class);
    // Route::resource('flash-sales', FlashSaleController::class);
});
