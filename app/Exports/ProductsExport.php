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
        return Product::with(['category', 'variants'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'SKU',
            'Price',
            'Stock Quantity',
            'Category',
            'Status',
            'Weight',
            'Dimensions',
            'Image URL',
            'Created At',
            'Updated At'
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->description,
            $product->sku,
            $product->price,
            $product->stock_quantity,
            $product->category->name ?? 'Uncategorized',
            $product->status,
            $product->weight,
            $product->dimensions,
            $product->image_url,
            $product->created_at->format('Y-m-d H:i:s'),
            $product->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
