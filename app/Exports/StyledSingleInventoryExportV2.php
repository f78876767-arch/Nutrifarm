<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StyledSingleInventoryExportV2 implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents, WithDrawings
{
    protected $rowCount = 0;

    public function collection()
    {
        $rows = collect();
        
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
        
        $this->rowCount = $rows->count();
        return $rows;
    }

    public function headings(): array
    {
        return ['Product Name','SKU','Category','Variants','Total Stock','Stock Value (Rp)','Status','Last Updated'];
    }

    public function title(): string
    {
        return 'Nutrifarm Inventory Report';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 38, 'B' => 18, 'C' => 24, 'D' => 12, 
            'E' => 16, 'F' => 20, 'G' => 16, 'H' => 22
        ];
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
                
                // Enhanced header row
                $sheet->getRowDimension(1)->setRowHeight(32);
                
                // Freeze pane and filter
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:H1');
                
                // Professional borders
                $sheet->getStyle("A1:H$highestRow")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A1:H1")->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_THICK);
                
                // Data row heights
                for($r = 2; $r <= $highestRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }
                
                // Number formatting
                $sheet->getStyle("E2:E$highestRow")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("F2:F$highestRow")->getNumberFormat()->setFormatCode('"Rp"\ #,##0');
                
                // Right align numbers
                $sheet->getStyle("D2:F$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                // Status styling with colors
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
                }
                
                // Zebra striping
                for($r = 2; $r <= $highestRow; $r++) {
                    if($r % 2 === 0) {
                        $sheet->getStyle("A$r:H$r")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                    }
                }
                
                // Low stock highlighting
                for($r = 2; $r <= $highestRow; $r++) {
                    $stockValue = $sheet->getCell("E$r")->getValue();
                    if(is_numeric($stockValue) && $stockValue <= 10) {
                        $sheet->getStyle("E$r")->getFont()->getColor()->setRGB('DC2626');
                        $sheet->getStyle("E$r")->getFont()->setBold(true);
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
