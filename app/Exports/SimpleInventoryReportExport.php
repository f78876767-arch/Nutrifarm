<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Variant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SimpleInventoryReportExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Nutrifarm Inventory Report' => new ModernStyledSheet(),
        ];
    }
}

class SimpleSummarySheet implements FromCollection, WithTitle, WithStyles, WithEvents, WithDrawings
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
            ['Generated At', now()->format('Y-m-d H:i')],
        ]);
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        // Basic styling handled here; advanced in events
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '0F172A']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getColumnDimension('A')->setWidth(28);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:B1');
                $sheet->getStyle("A1:B$highestRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('B2:B6')->getFont()->setBold(true);
                $sheet->getStyle("A1:B1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                // Zebra rows & subtle background
                for($r=2;$r<=$highestRow;$r++){
                    if($r % 2 === 0){
                        $sheet->getStyle("A$r:B$r")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
                    }
                }
            }
        ];
    }

    public function drawings()
    {
        $whiteLogo = base_path('nutrifarm_mobile/assets/images/nutrifarm_logo_putih.png');
        $colorLogo = public_path('images/nutrifarm_logo_1.png');
        $logo = file_exists($whiteLogo) ? $whiteLogo : (file_exists($colorLogo)? $colorLogo : null);
        if(!$logo) return [];
        $d = new Drawing();
        $d->setName('Nutrifarm');
        $d->setDescription('Nutrifarm');
        $d->setPath($logo);
        $d->setHeight(42);
        $d->setCoordinates('A1');
        return [$d];
    }
}

class SimpleProductsSheet implements FromCollection, WithTitle, WithStyles, WithEvents
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
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1E3A8A']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:E1");
                foreach(['A'=>30,'B'=>20,'C'=>20,'D'=>16,'E'=>16] as $col=>$w){
                    $sheet->getColumnDimension($col)->setWidth($w);
                }
                // Borders
                $sheet->getStyle("A1:E$highestRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                // Conditional formatting for stock
                $conditionalStyles = $sheet->getStyle("C2:C$highestRow")->getConditionalStyles();
                $low = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $low->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
                    ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHANOREQUAL)
                    ->addCondition('10');
                $low->getStyle()->getFont()->getColor()->setRGB('B91C1C');
                $low->getStyle()->getFont()->setBold(true);
                $conditionalStyles[] = $low;
                $sheet->getStyle("C2:C$highestRow")->setConditionalStyles($conditionalStyles);
                // Status pill styling + zebra rows
                for($r=2;$r<=$highestRow;$r++){
                    if($r % 2 === 0){
                        $sheet->getStyle("A$r:E$r")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                    }
                    $status = $sheet->getCell("E$r")->getValue();
                    $style = $sheet->getStyle("E$r");
                    $style->getFont()->setBold(true)->setSize(10);
                    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    if($status === 'Low Stock'){
                        $style->getFont()->getColor()->setRGB('92400E');
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
                    } elseif($status === 'Out of Stock'){
                        $style->getFont()->getColor()->setRGB('FFFFFF');
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DC2626');
                    } else {
                        $style->getFont()->getColor()->setRGB('065F46');
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ECFDF5');
                    }
                }
            }
        ];
    }
}

class ModernStyledSheet implements FromCollection, WithTitle, WithStyles, WithEvents, WithDrawings
{
    protected $rowCount = 0;

    public function collection()
    {
        $rows = collect();
        
        // Add header row
        $rows->push([
            'Product Name',
            'SKU',
            'Category',
            'Variants',
            'Total Stock',
            'Stock Value',
            'Status',
            'Last Updated'
        ]);
        
        Product::with(['category','variants'])->chunk(500, function($chunk) use (&$rows){
            foreach ($chunk as $product) {
                $totalStock = (int) $product->variants->sum('stock_quantity');
                $stockValue = 0;
                foreach ($product->variants as $variant) {
                    $stockValue += ($variant->base_price ?? 0) * ($variant->stock_quantity ?? 0);
                }
                $status = $totalStock === 0 ? 'Out of Stock' : ($totalStock <= 10 ? 'Low Stock' : 'In Stock');
                $rows->push([
                    $product->name,
                    $product->sku ?? '—',
                    $product->category->name ?? 'N/A',
                    $product->variants->count(),
                    $totalStock,
                    $stockValue,
                    $status,
                    $product->updated_at ? $product->updated_at->format('Y-m-d H:i') : null,
                ]);
            }
        });
        
        $this->rowCount = $rows->count() - 1; // Subtract header row for styling calculations
        return $rows;
    }

    public function title(): string
    {
        return 'Nutrifarm Inventory Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event){
                $sheet = $event->sheet->getDelegate();
                $highestRow = $this->rowCount + 1;
                
                // Header styling
                $sheet->getRowDimension(1)->setRowHeight(32);
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:H1');
                
                // Column widths
                $widths = ['A' => 38, 'B' => 18, 'C' => 24, 'D' => 12, 'E' => 16, 'F' => 20, 'G' => 16, 'H' => 22];
                foreach($widths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }
                
                // Borders
                $sheet->getStyle("A1:H$highestRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A1:H1")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
                
                // Data formatting
                for($r = 2; $r <= $highestRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }
                
                // Number formatting
                $sheet->getStyle("E2:E$highestRow")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("F2:F$highestRow")->getNumberFormat()->setFormatCode('"Rp"\ #,##0');
                $sheet->getStyle("D2:F$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                // Status pills
                for($r = 2; $r <= $highestRow; $r++) {
                    $status = $sheet->getCell("G$r")->getValue();
                    $style = $sheet->getStyle("G$r");
                    $style->getFont()->setBold(true);
                    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    if($status === 'Out of Stock') {
                        $style->getFont()->getColor()->setRGB('FFFFFF');
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DC2626');
                    } elseif($status === 'Low Stock') {
                        $style->getFont()->getColor()->setRGB('92400E');
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
                    } else {
                        $style->getFont()->getColor()->setRGB('065F46');
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ECFDF5');
                    }
                    
                    // Zebra striping
                    if($r % 2 === 0) {
                        $sheet->getStyle("A$r:H$r")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                    }
                }
                
                // Footer
                $footerRow = $highestRow + 2;
                $sheet->setCellValue("A$footerRow", "Generated: " . now()->format('d M Y, H:i') . " | Nutrifarm Inventory System");
                $sheet->getStyle("A$footerRow")->getFont()->setSize(8)->setItalic(true);
                $sheet->mergeCells("A$footerRow:H$footerRow");
            }
        ];
    }

    public function drawings()
    {
        $publicWhiteLogo = public_path('images/nutrifarm_logo_putih.png');
        $coloredLogo = public_path('images/nutrifarm_logo_1.png');
        
        $logoFile = file_exists($publicWhiteLogo) ? $publicWhiteLogo : 
                   (file_exists($coloredLogo) ? $coloredLogo : null);
        
        if(!$logoFile) { return []; }
        
        $drawing = new Drawing();
        $drawing->setName('Nutrifarm Logo');
        $drawing->setDescription('Nutrifarm Premium Inventory Report');
        $drawing->setPath($logoFile);
        $drawing->setHeight(28);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(2);
        
        return [$drawing];
    }
}
