<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Expected headers: name, description, category, variant_name, variant_value, base_price, stock_quantity, sku, is_active, discount_amount, weight, unit
        $categoryId = null;
        if (!empty($row['category'])) {
            $category = Category::firstOrCreate(['name' => trim($row['category'])]);
            $categoryId = $category->id;
        }

        $rawPrice = $row['base_price'] ?? $row['price'] ?? 0; // Support legacy 'price' column
        $price = is_numeric($rawPrice) ? $rawPrice : preg_replace('/[^\d.]/','', $rawPrice);

        $discount = null;
        if (!empty($row['discount_amount'])) {
            $rawDisc = $row['discount_amount'];
            $discount = is_numeric($rawDisc) ? $rawDisc : preg_replace('/[^\d.]/','', $rawDisc);
            if ($discount === '' || $discount <= 0) { $discount = null; }
        }

        $isActive = true;
        if (isset($row['is_active'])) {
            $val = strtolower(trim((string)$row['is_active']));
            $isActive = in_array($val, ['1','true','yes','active'], true);
        }

        // Create or find the product
        $product = Product::firstOrCreate([
            'name' => $row['name'],
        ], [
            'description' => $row['description'] ?? null,
            'category_id' => $categoryId,
            'is_active' => $isActive,
            'is_featured' => false,
        ]);

        // Create variant for the product
        $product->variants()->create([
            'name' => $row['variant_name'] ?? 'Default',
            'value' => $row['variant_value'] ?? 'Standard',
            'unit' => $row['unit'] ?? null,
            'sku' => $row['sku'] ?? null,
            'base_price' => $price,
            'stock_quantity' => $row['stock_quantity'] ?? 0,
            'discount_amount' => $discount,
            'weight' => $row['weight'] ?? null,
            'is_active' => $isActive,
        ]);

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'base_price' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0', // Legacy support
            'stock_quantity' => 'nullable|integer|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable',
            'variant_name' => 'nullable|string|max:255',
            'variant_value' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:32',
            'weight' => 'nullable|numeric|min:0',
        ];
    }
}
