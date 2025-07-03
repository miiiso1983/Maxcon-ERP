<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProductTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function array(): array
    {
        // Sample data to help users understand the format
        return [
            [
                'Paracetamol 500mg',           // name
                'PAR001',                      // sku
                '5000',                        // selling_price
                '3000',                        // cost_price
                '1234567890123',               // barcode
                'medicines',                   // category
                'Pain relief medication',      // description
                '100',                         // quantity
                '10',                          // min_quantity
                'box',                         // unit
                '0',                           // tax_rate
                'true',                        // is_active
                'true'                         // track_quantity
            ],
            [
                'Amoxicillin 250mg',
                'AMX001',
                '15000',
                '10000',
                '1234567890124',
                'medicines',
                'Antibiotic medication',
                '50',
                '5',
                'bottle',
                '0',
                'true',
                'true'
            ],
            [
                'Digital Thermometer',
                'THERM001',
                '25000',
                '18000',
                '1234567890125',
                'medical-devices',
                'Digital thermometer for accurate temperature measurement',
                '20',
                '3',
                'piece',
                '5',
                'true',
                'true'
            ],
            [
                'Vitamin D3 Tablets',
                'VIT001',
                '8000',
                '5000',
                '1234567890126',
                'supplements',
                'Vitamin D3 supplement for bone health',
                '200',
                '20',
                'pack',
                '0',
                'true',
                'true'
            ],
            [
                'Surgical Mask (50pcs)',
                'MASK001',
                '12000',
                '8000',
                '1234567890127',
                'medical-devices',
                'Disposable surgical masks - pack of 50',
                '100',
                '10',
                'pack',
                '0',
                'true',
                'true'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'name',              // Product Name (Required)
            'sku',               // SKU (Required)
            'selling_price',     // Selling Price (Required)
            'cost_price',        // Cost Price (Optional)
            'barcode',           // Barcode (Optional)
            'category',          // Category (Optional)
            'description',       // Description (Optional)
            'quantity',          // Initial Quantity (Optional)
            'min_quantity',      // Minimum Quantity (Optional)
            'unit',              // Unit (Optional)
            'tax_rate',          // Tax Rate % (Optional)
            'is_active',         // Is Active (Optional)
            'track_quantity'     // Track Quantity (Optional)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style the data rows
        $sheet->getStyle('A2:M6')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Alternate row colors for better readability
        for ($row = 2; $row <= 6; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:M{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ]);
            }
        }

        // Add instructions below the data
        $instructionRow = 8;
        $sheet->setCellValue("A{$instructionRow}", 'INSTRUCTIONS:');
        $sheet->getStyle("A{$instructionRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '2E75B6'],
            ],
        ]);

        $instructions = [
            'Required Fields: name, sku, selling_price',
            'Optional Fields: All other columns can be left empty',
            'Categories: medicines, medical-devices, supplements',
            'Units: piece, box, bottle, vial, pack',
            'Boolean Fields: Use true/false, 1/0, yes/no, or active/inactive',
            'Prices: Enter numeric values only (without currency symbols)',
            'SKU: Must be unique for each product',
            'Delete the sample rows above before importing your data',
            'Maximum 1000 products per file',
            'Supported formats: .xlsx, .xls, .csv'
        ];

        foreach ($instructions as $index => $instruction) {
            $row = $instructionRow + $index + 1;
            $sheet->setCellValue("A{$row}", "• {$instruction}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => [
                    'size' => 10,
                    'color' => ['rgb' => '666666'],
                ],
            ]);
        }

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // name
            'B' => 15,  // sku
            'C' => 15,  // selling_price
            'D' => 15,  // cost_price
            'E' => 18,  // barcode
            'F' => 18,  // category
            'G' => 30,  // description
            'H' => 12,  // quantity
            'I' => 15,  // min_quantity
            'J' => 12,  // unit
            'K' => 12,  // tax_rate
            'L' => 12,  // is_active
            'M' => 15,  // track_quantity
        ];
    }

    public function title(): string
    {
        return 'Products Template';
    }
}
