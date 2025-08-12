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
        // Find or create category
        $category = null;
        if (!empty($row['category'])) {
            $category = Category::firstOrCreate(['name' => $row['category']]);
        }

        return new Product([
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'sku' => $row['sku'] ?? null,
            'price' => $row['price'],
            'stock_quantity' => $row['stock_quantity'] ?? 0,
            'category_id' => $category ? $category->id : null,
            'status' => $row['status'] ?? 'active',
            'weight' => $row['weight'] ?? null,
            'dimensions' => $row['dimensions'] ?? null,
            'image_url' => $row['image_url'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'status' => 'in:active,inactive',
        ];
    }
}
