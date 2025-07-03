<?php

namespace App\Services;

use App\Modules\Reports\Models\ReportExecution;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportExportService
{
    public function export(ReportExecution $execution, string $format = 'pdf'): string
    {
        $data = $execution->result_data;
        $reportName = $execution->report->name;
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "report_{$execution->id}_{$timestamp}";

        switch (strtolower($format)) {
            case 'excel':
                return $this->exportToExcel($data, $reportName, $filename);
            case 'csv':
                return $this->exportToCsv($data, $reportName, $filename);
            case 'pdf':
            default:
                return $this->exportToPdf($data, $reportName, $filename, $execution);
        }
    }

    private function exportToExcel(array $data, string $reportName, string $filename): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setTitle($reportName);
        $sheet->setCellValue('A1', $reportName);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        // Add export info
        $sheet->setCellValue('A2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
        $sheet->setCellValue('A3', 'Total Records: ' . count($data));
        
        if (empty($data)) {
            $sheet->setCellValue('A5', 'No data available');
        } else {
            // Add headers
            $headers = array_keys($data[0]);
            $col = 'A';
            $row = 5;
            
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, ucwords(str_replace('_', ' ', $header)));
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }
            
            // Add data
            $row = 6;
            foreach ($data as $record) {
                $col = 'A';
                foreach ($record as $value) {
                    $sheet->setCellValue($col . $row, $this->formatCellValue($value));
                    $col++;
                }
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', $col) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
        }
        
        $writer = new Xlsx($spreadsheet);
        $filePath = "exports/{$filename}.xlsx";
        $fullPath = storage_path('app/' . $filePath);
        
        // Ensure directory exists
        Storage::makeDirectory('exports');
        
        $writer->save($fullPath);
        
        return $filePath;
    }

    private function exportToCsv(array $data, string $reportName, string $filename): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        if (empty($data)) {
            $sheet->setCellValue('A1', 'No data available');
        } else {
            // Add headers
            $headers = array_keys($data[0]);
            $col = 'A';
            $row = 1;
            
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, ucwords(str_replace('_', ' ', $header)));
                $col++;
            }
            
            // Add data
            $row = 2;
            foreach ($data as $record) {
                $col = 'A';
                foreach ($record as $value) {
                    $sheet->setCellValue($col . $row, $this->formatCellValue($value));
                    $col++;
                }
                $row++;
            }
        }
        
        $writer = new Csv($spreadsheet);
        $filePath = "exports/{$filename}.csv";
        $fullPath = storage_path('app/' . $filePath);
        
        // Ensure directory exists
        Storage::makeDirectory('exports');
        
        $writer->save($fullPath);
        
        return $filePath;
    }

    private function exportToPdf(array $data, string $reportName, string $filename, ReportExecution $execution): string
    {
        $html = $this->generatePdfHtml($data, $reportName, $execution);
        
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filePath = "exports/{$filename}.pdf";
        $fullPath = storage_path('app/' . $filePath);
        
        // Ensure directory exists
        Storage::makeDirectory('exports');
        
        file_put_contents($fullPath, $dompdf->output());
        
        return $filePath;
    }

    private function generatePdfHtml(array $data, string $reportName, ReportExecution $execution): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>' . htmlspecialchars($reportName) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                    margin: 20px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 10px;
                }
                .header h1 {
                    margin: 0;
                    color: #333;
                }
                .info {
                    margin-bottom: 20px;
                    background-color: #f8f9fa;
                    padding: 10px;
                    border-radius: 5px;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 5px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
                .no-data {
                    text-align: center;
                    padding: 40px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>' . htmlspecialchars($reportName) . '</h1>
            </div>
            
            <div class="info">
                <div class="info-row">
                    <span><strong>Generated on:</strong> ' . now()->format('Y-m-d H:i:s') . '</span>
                    <span><strong>Report Type:</strong> ' . ucfirst($execution->report->report_type) . '</span>
                </div>
                <div class="info-row">
                    <span><strong>Total Records:</strong> ' . count($data) . '</span>
                    <span><strong>Execution Time:</strong> ' . $execution->duration_text . '</span>
                </div>
                <div class="info-row">
                    <span><strong>Generated by:</strong> ' . $execution->executedBy->name . '</span>
                    <span><strong>Category:</strong> ' . ucfirst($execution->report->category) . '</span>
                </div>
            </div>';

        if (empty($data)) {
            $html .= '<div class="no-data">No data available for the selected criteria.</div>';
        } else {
            $html .= '<table>';
            
            // Headers
            $headers = array_keys($data[0]);
            $html .= '<thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
            }
            $html .= '</tr></thead>';
            
            // Data
            $html .= '<tbody>';
            foreach ($data as $record) {
                $html .= '<tr>';
                foreach ($record as $value) {
                    $html .= '<td>' . htmlspecialchars($this->formatCellValue($value)) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            
            $html .= '</table>';
        }

        $html .= '
            <div class="footer">
                <p>Generated by Maxcon ERP System | ' . config('app.name') . '</p>
                <p>This report contains confidential business information</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function formatCellValue($value): string
    {
        if (is_null($value)) {
            return '';
        }
        
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        
        if (is_numeric($value) && strpos($value, '.') !== false) {
            return number_format((float)$value, 2);
        }
        
        return (string)$value;
    }

    public function getExportFormats(): array
    {
        return [
            'pdf' => [
                'name' => 'PDF Document',
                'extension' => 'pdf',
                'mime_type' => 'application/pdf',
                'icon' => 'fas fa-file-pdf',
                'color' => 'danger',
            ],
            'excel' => [
                'name' => 'Excel Spreadsheet',
                'extension' => 'xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'icon' => 'fas fa-file-excel',
                'color' => 'success',
            ],
            'csv' => [
                'name' => 'CSV File',
                'extension' => 'csv',
                'mime_type' => 'text/csv',
                'icon' => 'fas fa-file-csv',
                'color' => 'info',
            ],
        ];
    }

    public function cleanupOldExports(int $daysOld = 7): int
    {
        $cutoffDate = now()->subDays($daysOld);
        $exportPath = storage_path('app/exports');
        $deletedCount = 0;

        if (!is_dir($exportPath)) {
            return 0;
        }

        $files = glob($exportPath . '/*');
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffDate->timestamp) {
                unlink($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
