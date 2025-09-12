<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Return variant data instead of product data
        $products = Product::with(['category', 'variants'])->get();
        
        $variants = collect();
        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $variants->push((object) [
                    'product' => $product,
                    'variant' => $variant,
                ]);
            }
        }
        
        return $variants;
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Name',
            'Description',
            'Category',
            'Variant Name',
            'Variant Value',
            'Base Price',
            'Stock Quantity',
            'SKU',
            'Is Active',
            'Discount Amount',
            'Weight',
            'Unit',
            'Created At',
            'Updated At'
        ];
    }

    public function map($row): array
    {
        $product = $row->product;
        $variant = $row->variant;
        
        return [
            $product->id,
            $product->name,
            $product->description,
            $product->category->name ?? 'Uncategorized',
            $variant->name,
            $variant->value,
            $variant->base_price,
            $variant->stock_quantity,
            $variant->sku,
            $variant->is_active ? 'true' : 'false',
            $variant->discount_amount,
            $variant->weight,
            $variant->unit,
            $product->created_at->format('Y-m-d H:i:s'),
            $product->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
