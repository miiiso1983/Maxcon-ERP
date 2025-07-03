<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SuppliersPerformanceExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    WithTitle, 
    WithColumnWidths,
    WithEvents
{
    protected $suppliers;
    protected $filters;

    public function __construct($suppliers, $filters = [])
    {
        $this->suppliers = $suppliers;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->suppliers;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Supplier Code',
            'Supplier Name',
            'Contact Person',
            'Phone',
            'Email',
            'Type',
            'Payment Terms',
            'Credit Limit (IQD)',
            'Total Orders',
            'Total Spent (IQD)',
            'Rating',
            'Status',
            'Last Order Date'
        ];
    }

    public function map($supplier): array
    {
        $lastOrderDate = $supplier['last_order'] 
            ? \Carbon\Carbon::parse($supplier['last_order'])->format('Y-m-d')
            : 'No orders yet';

        return [
            $supplier['id'],
            $supplier['supplier_code'] ?? 'N/A',
            $supplier['name'],
            $supplier['contact_person'] ?? 'N/A',
            $supplier['phone'] ?? 'N/A',
            $supplier['email'] ?? 'N/A',
            $supplier['supplier_type'],
            $supplier['payment_terms'] ?? 'N/A',
            $supplier['credit_limit'] ?? 0,
            $supplier['total_orders'],
            $supplier['total_spent'],
            $supplier['rating'],
            $supplier['status'],
            $lastOrderDate
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0d6efd'],
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
            ],
            // Style data rows
            'A2:N' . ($this->suppliers->count() + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Style numeric columns
            'I:K' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
            ],
            // Style rating column
            'L:L' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'numberFormat' => [
                    'formatCode' => '0.0',
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Suppliers Performance Report';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 15,  // Supplier Code
            'C' => 25,  // Supplier Name
            'D' => 20,  // Contact Person
            'E' => 15,  // Phone
            'F' => 25,  // Email
            'G' => 15,  // Type
            'H' => 15,  // Payment Terms
            'I' => 18,  // Credit Limit
            'J' => 12,  // Total Orders
            'K' => 18,  // Total Spent
            'L' => 10,  // Rating
            'M' => 12,  // Status
            'N' => 15,  // Last Order Date
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add summary section
                $lastRow = $this->suppliers->count() + 3;
                
                // Summary title
                $sheet->setCellValue('A' . $lastRow, 'REPORT SUMMARY');
                $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A' . $lastRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('f8f9fa');
                
                // Summary data
                $summaryData = [
                    ['Total Suppliers:', $this->suppliers->count()],
                    ['Active Suppliers:', $this->suppliers->where('is_active', true)->count()],
                    ['Inactive Suppliers:', $this->suppliers->where('is_active', false)->count()],
                    ['Total Orders:', $this->suppliers->sum('total_orders')],
                    ['Total Spent:', number_format($this->suppliers->sum('total_spent'), 2) . ' IQD'],
                    ['Average Rating:', number_format($this->suppliers->avg('rating'), 2)],
                    ['Total Credit Limit:', number_format($this->suppliers->sum('credit_limit'), 2) . ' IQD'],
                    ['Report Generated:', now()->format('Y-m-d H:i:s')],
                ];

                foreach ($summaryData as $index => $data) {
                    $row = $lastRow + $index + 1;
                    $sheet->setCellValue('A' . $row, $data[0]);
                    $sheet->setCellValue('B' . $row, $data[1]);
                    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                }

                // Add filters info if any
                if (!empty($this->filters)) {
                    $filtersRow = $lastRow + count($summaryData) + 2;
                    $sheet->setCellValue('A' . $filtersRow, 'APPLIED FILTERS:');
                    $sheet->getStyle('A' . $filtersRow)->getFont()->setBold(true);
                    
                    $filterIndex = 1;
                    foreach ($this->filters as $key => $value) {
                        if ($value) {
                            $sheet->setCellValue('A' . ($filtersRow + $filterIndex), ucfirst(str_replace('_', ' ', $key)) . ':');
                            $sheet->setCellValue('B' . ($filtersRow + $filterIndex), $value);
                            $filterIndex++;
                        }
                    }
                }

                // Auto-fit columns
                foreach (range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(false);
                }

                // Freeze header row
                $sheet->freezePane('A2');
            },
        ];
    }
}
