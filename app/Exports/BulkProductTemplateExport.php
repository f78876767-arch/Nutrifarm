<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BulkProductTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Template';
    }

    public function headings(): array
    {
        return [
            'name',
            'description',
            'category',
            'variant_name',
            'variant_value',
            'base_price',
            'stock_quantity',
            'sku',
            'is_active',
            'discount_amount',
            'weight',
            'unit',
            'image_mode', // url | filename
            'image_1',
            'image_2',
            'image_3'
        ];
    }

    public function array(): array
    {
        return [
            [
                'Sample Product A',
                'Deskripsi singkat produk A',
                'Kategori A',
                'Ukuran',
                'Large',
                '150000',
                '25',
                'SKU-A-001',
                'true',
                '0',
                '500',
                'gram',
                'url',
                'https://via.placeholder.com/600?text=Product+A',
                '',
                ''
            ],
            [
                'Sample Product B',
                'Contoh dengan gambar via filename (upload ZIP)',
                'Kategori B',
                'Warna',
                'Merah',
                '99000',
                '10',
                'SKU-B-RED',
                'true',
                '5000',
                '250',
                'ml',
                'filename',
                'product_b_main.jpg',
                'product_b_side.jpg',
                ''
            ]
        ];
    }
}
