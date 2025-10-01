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

class StyledSingleInventoryExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents, WithDrawings
{
    protected $rowCount = 0;

    public function collection()
    {
        $rows = collect();
        $rows->push([
            'Product Name','SKU','Category','Variants','Total Stock','Stock Value (Rp)','Status','Last Updated'
        ]); // header placeholder counted separately by Excel

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
        // Remove first header duplicate for Maatwebsite heading handling (we supply headings())
        return $rows->skip(1);
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
            'A' => 38,  // Product Name - wider for better readability
            'B' => 18,  // SKU - slightly wider
            'C' => 24,  // Category - wider
            'D' => 12,  // Variants - optimal for numbers
            'E' => 16,  // Total Stock - wider for formatted numbers
            'F' => 20,  // Stock Value - wider for Rupiah formatting
            'G' => 16,  // Status - wider for pill styling
            'H' => 22   // Last Updated - wider for timestamp
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold'=>true,'size'=>11,'color'=>['rgb'=>'FFFFFF']],
                'fill' => ['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'1E3A8A']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event){
                $sheet = $event->sheet->getDelegate();
                $highestRow = $this->rowCount + 1; // headings included
                
                // === PREMIUM HEADER STYLING ===
                $sheet->getRowDimension(1)->setRowHeight(35);
                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 13,
                        'name' => 'Segoe UI',
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '1E293B'] // slate-800
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '334155'] // slate-700
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '6366F1'] // indigo-500
                        ]
                    ]
                ]);

                // === ENHANCED DATA ROW STYLING ===
                for($r = 2; $r <= $highestRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(22);
                }

                // Professional table borders with modern colors
                $sheet->getStyle("A1:H$highestRow")->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_THICK)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('475569')); // slate-600

                $sheet->getStyle("A1:H$highestRow")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('E2E8F0')); // slate-200

                // === MODERN TYPOGRAPHY & ALIGNMENT ===
                $sheet->getStyle("A2:H$highestRow")->applyFromArray([
                    'font' => [
                        'name' => 'Segoe UI',
                        'size' => 10
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => false
                    ]
                ]);

                // Right-align numeric columns
                $sheet->getStyle("D2:F$highestRow")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // === PREMIUM NUMBER FORMATTING ===
                $sheet->getStyle("E2:E$highestRow")->getNumberFormat()
                    ->setFormatCode('#,##0'); // Stock quantity with separators
                $sheet->getStyle("F2:F$highestRow")->getNumberFormat()
                    ->setFormatCode('"Rp"\ #,##0_-'); // Rupiah with trailing alignment

                // === ENHANCED CONDITIONAL FORMATTING ===
                
                // Critical stock levels (≤5) - Red alert
                $criticalRange = "E2:E$highestRow";
                $criticalConds = $sheet->getStyle($criticalRange)->getConditionalStyles();
                $critical = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $critical->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
                    ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHANOREQUAL)
                    ->addCondition('5');
                $critical->getStyle()->getFont()->getColor()->setRGB('FFFFFF');
                $critical->getStyle()->getFont()->setBold(true);
                $critical->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DC2626');
                $criticalConds[] = $critical;

                // Low stock levels (6-10) - Orange warning
                $lowStock = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $lowStock->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
                    ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_BETWEEN)
                    ->addCondition('6')->addCondition('10');
                $lowStock->getStyle()->getFont()->getColor()->setRGB('92400E');
                $lowStock->getStyle()->getFont()->setBold(true);
                $lowStock->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FED7AA');
                $criticalConds[] = $lowStock;
                
                $sheet->getStyle($criticalRange)->setConditionalStyles($criticalConds);

                // Stock value gradient (simplified)
                $rangeValue = "F2:F$highestRow";
                $scale = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $scale->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_COLOR_SCALE);
                $scaleCfg = $scale->getColorScale();
                $scaleCfg->setCFVOs([
                    ['type' => 'min'],
                    ['type' => 'max']
                ]);
                $scaleCfg->setColors([
                    new \PhpOffice\PhpSpreadsheet\Style\Color('FEE2E2'), // red-100
                    new \PhpOffice\PhpSpreadsheet\Style\Color('D1FAE5')  // green-100
                ]);
                $valConds = $sheet->getStyle($rangeValue)->getConditionalStyles();
                $valConds[] = $scale;
                $sheet->getStyle($rangeValue)->setConditionalStyles($valConds);

                // === PREMIUM STATUS PILLS ===
                for($r = 2; $r <= $highestRow; $r++) {
                    $status = $sheet->getCell("G$r")->getValue();
                    $style = $sheet->getStyle("G$r");
                    
                    $style->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 9,
                            'name' => 'Segoe UI Semibold'
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'FFFFFF']
                            ]
                        ]
                    ]);

                    if($status === 'Out of Stock') {
                        $style->getFont()->getColor()->setRGB('FFFFFF');
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EF4444'); // red-500
                    } elseif($status === 'Low Stock') {
                        $style->getFont()->getColor()->setRGB('FFFFFF');
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F59E0B'); // amber-500
                    } else {
                        $style->getFont()->getColor()->setRGB('FFFFFF');
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('10B981'); // emerald-500
                    }
                }

                // === ELEGANT ZEBRA STRIPING ===
                for($r = 2; $r <= $highestRow; $r++) {
                    if($r % 2 === 0) {
                        $sheet->getStyle("A$r:H$r")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC'); // slate-50
                    }
                }

                // === PROFESSIONAL TOUCHES ===
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:H1');
                
                // Add footer information
                $footerRow = $highestRow + 2;
                $sheet->setCellValue("A$footerRow", "Generated: " . now()->format('d M Y, H:i') . " | Nutrifarm Inventory System");
                $sheet->getStyle("A$footerRow")->applyFromArray([
                    'font' => [
                        'size' => 8,
                        'italic' => true,
                        'color' => ['rgb' => '64748B'] // slate-500
                    ]
                ]);
                $sheet->mergeCells("A$footerRow:H$footerRow");
            }
        ];
    }

    public function drawings()
    {
        // Use white logo from public directory, fallback to mobile assets, then colored logo
        $publicWhiteLogo = public_path('images/nutrifarm_logo_putih.png');
        $mobileWhiteLogo = base_path('nutrifarm_mobile/assets/images/nutrifarm_logo_putih.png');
        $coloredLogo = public_path('images/nutrifarm_logo_1.png');
        
        $logoFile = null;
        if(file_exists($publicWhiteLogo)) {
            $logoFile = $publicWhiteLogo;
        } elseif(file_exists($mobileWhiteLogo)) {
            $logoFile = $mobileWhiteLogo;
        } elseif(file_exists($coloredLogo)) {
            $logoFile = $coloredLogo;
        }
        
        if(!$logoFile) { return []; }
        
        $drawing = new Drawing();
        $drawing->setName('Nutrifarm Logo');
        $drawing->setDescription('Nutrifarm Premium Inventory Report');
        $drawing->setPath($logoFile);
        $drawing->setHeight(32); // Smaller to fit better with enhanced header
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(8);  // Small offset from cell edge
        $drawing->setOffsetY(2);  // Small vertical offset
        
        return [$drawing];
    }
}
