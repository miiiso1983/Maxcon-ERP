<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicTemplateController extends Controller
{
    /**
     * Download suppliers template
     */
    public function suppliersTemplate()
    {
        $templatePath = public_path('templates/suppliers_template.csv');
        
        // If template file exists, return it
        if (file_exists($templatePath)) {
            return response()->download($templatePath, 'suppliers_import_template.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Transfer-Encoding' => 'binary'
            ]);
        }
        
        // Fallback: Generate template content
        $csvContent = '';
        
        // Add header row with clear column names
        $headers = [
            'name (اسم المورد)',
            'phone (رقم الهاتف)',
            'email (البريد الإلكتروني)',
            'address (العنوان)',
            'city (المدينة)',
            'supplier_type (نوع المورد)',
            'contact_person (الشخص المسؤول)',
            'tax_number (رقم الضريبة)',
            'license_number (رقم الترخيص)',
            'payment_terms (شروط الدفع)',
            'credit_limit (حد الائتمان)',
            'notes (ملاحظات)'
        ];
        
        $csvContent .= '"' . implode('","', $headers) . '"' . "\n";
        
        // Add sample data rows
        $sampleData = [
            [
                'Medical Supplies International',
                '+964 1 234 5678',
                'info@medicalsupplies.com',
                'Baghdad, Al-Karrada District, Medical Complex',
                'baghdad',
                'distributor',
                'Ahmed Al-Rashid',
                'TAX123456789',
                'LIC987654321',
                'net_30',
                '5000000',
                'Reliable supplier for medical equipment and pharmaceuticals'
            ],
            [
                'Pharma Distribution Co.',
                '+964 1 345 6789',
                'orders@pharmadist.com',
                'Basra, Industrial Zone, Building 15',
                'basra',
                'wholesaler',
                'Fatima Hassan',
                'TAX456789123',
                'LIC654321987',
                'net_15',
                '3000000',
                'Pharmaceutical products specialist with fast delivery'
            ],
            [
                'Equipment Solutions Ltd.',
                '+964 1 456 7890',
                'sales@equipmentsolutions.com',
                'Erbil, Technology Park, Unit 8',
                'erbil',
                'manufacturer',
                'Omar Khalil',
                'TAX789123456',
                'LIC321987654',
                'net_60',
                '8000000',
                'Medical equipment manufacturer with warranty support'
            ]
        ];
        
        // Add sample data to CSV
        foreach ($sampleData as $row) {
            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="suppliers_import_template.csv"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Download customers template
     */
    public function customersTemplate()
    {
        $templatePath = public_path('templates/customers_template.csv');
        
        // If template file exists, return it
        if (file_exists($templatePath)) {
            return response()->download($templatePath, 'customers_import_template.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Transfer-Encoding' => 'binary'
            ]);
        }
        
        // Fallback: Generate template content
        $csvContent = '';
        
        // Add header row with clear column names
        $headers = [
            'name (الاسم الكامل)',
            'phone (رقم الهاتف)',
            'email (البريد الإلكتروني)',
            'address (العنوان)',
            'city (المدينة)',
            'district (المنطقة)',
            'customer_type (نوع العميل)',
            'credit_limit (حد الائتمان)',
            'payment_terms (شروط الدفع)',
            'tax_number (رقم الضريبة)',
            'license_number (رقم الترخيص)',
            'notes (ملاحظات)'
        ];
        
        $csvContent .= '"' . implode('","', $headers) . '"' . "\n";
        
        // Add sample data rows
        $sampleData = [
            [
                'Ahmed Al-Rashid',
                '+964 770 123 4567',
                'ahmed@email.com',
                'Baghdad, Al-Karrada District, Street 14, Building 25',
                'baghdad',
                'Al-Karrada',
                'individual',
                '0',
                'cash',
                '',
                '',
                'Regular customer, prefers morning deliveries'
            ],
            [
                'Fatima Hassan',
                '+964 771 234 5678',
                'fatima@email.com',
                'Basra, Al-Ashar District, Medical Street 5',
                'basra',
                'Al-Ashar',
                'pharmacy',
                '500000',
                'net_30',
                'TAX123456',
                'LIC789012',
                'Pharmacy owner, bulk orders discount eligible'
            ],
            [
                'Omar Khalil',
                '+964 772 345 6789',
                'omar@email.com',
                'Erbil, Downtown, Hospital Complex',
                'erbil',
                'Downtown',
                'hospital',
                '1000000',
                'net_15',
                'TAX654321',
                'LIC345678',
                'Hospital procurement manager, urgent orders'
            ]
        ];
        
        // Add sample data to CSV
        foreach ($sampleData as $row) {
            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="customers_import_template.csv"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Export suppliers report
     */
    public function exportSuppliers(Request $request)
    {
        $format = $request->input('format', 'excel');

        // Sample suppliers data for demo
        $suppliers = collect([
            [
                'id' => 1,
                'name' => 'Baghdad Medical Supplies',
                'contact_person' => 'Ali Mohammed',
                'phone' => '+964 1 234 5678',
                'email' => 'ali@baghdadmedical.com',
                'supplier_type' => 'Distributor',
                'total_orders' => 45,
                'total_spent' => 2500000,
                'rating' => 4.5,
                'status' => 'Active',
                'last_order' => now()->subDays(3)->format('Y-m-d')
            ],
            [
                'id' => 2,
                'name' => 'Kurdistan Pharmaceuticals',
                'contact_person' => 'Sara Ahmed',
                'phone' => '+964 66 123 4567',
                'email' => 'sara@kurdistanpharma.com',
                'supplier_type' => 'Manufacturer',
                'total_orders' => 32,
                'total_spent' => 1800000,
                'rating' => 4.2,
                'status' => 'Active',
                'last_order' => now()->subDays(8)->format('Y-m-d')
            ],
            [
                'id' => 3,
                'name' => 'Basra Equipment Co.',
                'contact_person' => 'Omar Hassan',
                'phone' => '+964 40 987 6543',
                'email' => 'omar@basraequipment.com',
                'supplier_type' => 'Wholesaler',
                'total_orders' => 28,
                'total_spent' => 1650000,
                'rating' => 4.0,
                'status' => 'Active',
                'last_order' => now()->subDays(12)->format('Y-m-d')
            ]
        ]);

        if ($format === 'pdf') {
            return $this->exportSuppliersPDF($suppliers);
        } else {
            return $this->exportSuppliersExcel($suppliers);
        }
    }

    private function exportSuppliersExcel($suppliers)
    {
        $csvContent = '';

        // Add header
        $headers = [
            'ID',
            'Supplier Name',
            'Contact Person',
            'Phone',
            'Email',
            'Type',
            'Total Orders',
            'Total Spent (IQD)',
            'Rating',
            'Status',
            'Last Order Date'
        ];

        $csvContent .= '"' . implode('","', $headers) . '"' . "\n";

        // Add data rows
        foreach ($suppliers as $supplier) {
            $row = [
                $supplier['id'],
                $supplier['name'],
                $supplier['contact_person'],
                $supplier['phone'],
                $supplier['email'],
                $supplier['supplier_type'],
                $supplier['total_orders'],
                number_format($supplier['total_spent']),
                $supplier['rating'],
                $supplier['status'],
                $supplier['last_order']
            ];
            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        // Add summary
        $csvContent .= "\n";
        $csvContent .= "REPORT SUMMARY\n";
        $csvContent .= "Total Suppliers," . $suppliers->count() . "\n";
        $csvContent .= "Active Suppliers," . $suppliers->where('status', 'Active')->count() . "\n";
        $csvContent .= "Total Orders," . $suppliers->sum('total_orders') . "\n";
        $csvContent .= "Total Spent," . number_format($suppliers->sum('total_spent')) . " IQD\n";
        $csvContent .= "Average Rating," . number_format($suppliers->avg('rating'), 2) . "\n";
        $csvContent .= "Report Generated," . now()->format('Y-m-d H:i:s') . "\n";

        $filename = 'suppliers_performance_report_' . date('Y-m-d_H-i-s') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    private function exportSuppliersPDF($suppliers)
    {
        // For now, return CSV format as PDF generation requires additional setup
        return $this->exportSuppliersExcel($suppliers);
    }
}
