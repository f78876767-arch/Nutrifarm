<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Variant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SimpleInventoryReportExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Summary' => new SimpleSummarySheet(),
            'Products' => new SimpleProductsSheet(),
        ];
    }
}

class SimpleSummarySheet implements FromCollection, WithTitle, WithStyles
{
    public function collection()
    {
        $totalProducts = Product::count();
        $totalVariants = Variant::sum('stock_quantity');
        $categoriesCount = Category::count();
        $lowStockCount = Variant::where('stock_quantity', '<=', 10)->where('stock_quantity', '>', 0)->count();
        $outOfStockCount = Variant::where('stock_quantity', 0)->count();

        return collect([
            ['Metric', 'Value'],
            ['Total Products', $totalProducts],
            ['Total Stock Units', $totalVariants],
            ['Categories', $categoriesCount],
            ['Low Stock Items', $lowStockCount],
            ['Out of Stock Items', $outOfStockCount],
            ['Report Generated', now()->format('Y-m-d H:i:s')],
        ]);
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F81BD']],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}

class SimpleProductsSheet implements FromCollection, WithTitle, WithStyles
{
    public function collection()
    {
        $products = Product::with(['category', 'variants'])->get();
        $data = collect([['Product Name', 'Category', 'Total Stock', 'Variants Count', 'Status']]);
        
        foreach ($products as $product) {
            $totalStock = $product->variants->sum('stock_quantity');
            $status = $totalStock > 10 ? 'Good Stock' : ($totalStock > 0 ? 'Low Stock' : 'Out of Stock');
            
            $data->push([
                $product->name,
                $product->category->name ?? 'N/A',
                $totalStock,
                $product->variants->count(),
                $status
            ]);
        }
        
        return $data;
    }

    public function title(): string
    {
        return 'Products';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F81BD']],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}
