<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "=== Testing New Variant-Based Structure ===\n\n";

// Create a test product with variants
$category = Category::firstOrCreate(['name' => 'Test Category']);

$product = Product::create([
    'name' => 'Test Product with Variants',
    'description' => 'This is a test product to verify variant functionality',
    'category_id' => $category->id,
    'is_active' => true,
    'is_featured' => false,
]);

echo "Created product: {$product->name} (ID: {$product->id})\n";

// Create multiple variants
$variants = [
    [
        'name' => 'Size',
        'value' => '250ml',
        'unit' => 'ml',
        'base_price' => 15000,
        'stock_quantity' => 50,
        'discount_amount' => 2000,
        'sku' => 'TEST-250ML',
        'weight' => 0.3,
    ],
    [
        'name' => 'Size', 
        'value' => '500ml',
        'unit' => 'ml',
        'base_price' => 25000,
        'stock_quantity' => 30,
        'discount_amount' => null,
        'sku' => 'TEST-500ML',
        'weight' => 0.6,
    ],
    [
        'name' => 'Size',
        'value' => '1 Liter',
        'unit' => 'l',
        'base_price' => 45000,
        'stock_quantity' => 20,
        'discount_amount' => 5000,
        'sku' => 'TEST-1L',
        'weight' => 1.2,
    ],
];

foreach ($variants as $variantData) {
    $variant = $product->variants()->create($variantData + ['is_active' => true]);
    echo "  Created variant: {$variant->name} - {$variant->value} (Price: {$variant->base_price}, Effective: {$variant->effective_price})\n";
}

echo "\n=== Testing Product Methods ===\n";

// Test product methods
$product = $product->fresh(['variants']);

echo "Primary variant: " . ($product->primaryVariant() ? $product->primaryVariant()->value : 'None') . "\n";
echo "Min price: {$product->min_price}\n";
echo "Max price: {$product->max_price}\n";
echo "Effective price (from primary): {$product->effective_price}\n";
echo "Has active promotions: " . ($product->hasActivePromotions() ? 'Yes' : 'No') . "\n";
echo "Is discount active: " . ($product->is_discount_active ? 'Yes' : 'No') . "\n";

echo "\n=== Testing Variant Methods ===\n";

foreach ($product->variants as $variant) {
    echo "Variant {$variant->value}:\n";
    echo "  Base price: {$variant->base_price}\n";
    echo "  Discount: " . ($variant->discount_amount ?? 'None') . "\n";
    echo "  Effective price: {$variant->effective_price}\n";
    echo "  Is discount active: " . ($variant->isDiscountActive() ? 'Yes' : 'No') . "\n";
    echo "  Stock: {$variant->stock_quantity}\n\n";
}

echo "=== API Response Simulation ===\n";

// Simulate API response
$apiData = [
    'id' => $product->id,
    'name' => $product->name,
    'description' => $product->description,
    'price' => $product->primaryVariant() ? (float)$product->primaryVariant()->base_price : 0,
    'effective_price' => (float)$product->effective_price,
    'discount_amount' => $product->is_discount_active ? ($product->primaryVariant() ? (float)$product->primaryVariant()->discount_amount : null) : null,
    'is_discount_active' => (bool)$product->is_discount_active,
    'variants' => $product->variants->map(function($v) {
        return [
            'id' => $v->id,
            'name' => $v->name,
            'value' => $v->value,
            'unit' => $v->unit,
            'sku' => $v->sku,
            'base_price' => (float)$v->base_price,
            'effective_price' => (float)$v->effective_price,
            'discount_amount' => $v->isDiscountActive() ? (float)$v->discount_amount : null,
            'is_discount_active' => $v->isDiscountActive(),
            'stock_quantity' => (int)$v->stock_quantity,
            'weight' => $v->weight,
        ];
    })->toArray(),
];

echo json_encode($apiData, JSON_PRETTY_PRINT) . "\n";

echo "\n=== Test Completed Successfully! ===\n";

// Clean up test data
$product->variants()->delete();
$product->delete();
echo "Test data cleaned up.\n";
