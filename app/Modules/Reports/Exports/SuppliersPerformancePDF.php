<?php

namespace App\Modules\Reports\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

class SuppliersPerformancePDF
{
    protected $suppliers;
    protected $filters;
    protected $options;

    public function __construct($suppliers, $filters = [], $options = [])
    {
        $this->suppliers = $suppliers;
        $this->filters = $filters;
        $this->options = array_merge([
            'orientation' => 'landscape',
            'paper_size' => 'A4',
            'font' => 'DejaVu Sans',
            'include_summary' => true,
            'include_filters' => true,
            'currency' => 'IQD',
        ], $options);
    }

    public function generate()
    {
        try {
            // Prepare data for PDF
            $data = [
                'suppliers' => $this->suppliers,
                'filters' => $this->filters,
                'currency' => $this->options['currency'],
                'generated_at' => now(),
                'options' => $this->options,
                'statistics' => $this->calculateStatistics(),
            ];

            // Generate PDF using DomPDF
            $pdf = Pdf::loadView('tenant.reports.suppliers-pdf', $data);
            
            // Configure PDF settings
            $pdf->setPaper($this->options['paper_size'], $this->options['orientation'])
                ->setOptions([
                    'defaultFont' => $this->options['font'],
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'chroot' => public_path(),
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                    'debugLayoutLines' => false,
                    'debugLayoutBlocks' => false,
                    'debugLayoutInline' => false,
                    'debugLayoutPaddingBox' => false,
                ]);

            return $pdf;

        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage(), [
                'suppliers_count' => $this->suppliers->count(),
                'filters' => $this->filters,
                'options' => $this->options,
                'error_trace' => $e->getTraceAsString(),
            ]);

            // Try to provide more specific error information
            $errorMessage = 'Failed to generate PDF report';
            if (strpos($e->getMessage(), 'font') !== false) {
                $errorMessage .= ': Font loading issue. Please check font configuration.';
            } elseif (strpos($e->getMessage(), 'memory') !== false) {
                $errorMessage .= ': Memory limit exceeded. Try reducing the data size.';
            } elseif (strpos($e->getMessage(), 'view') !== false) {
                $errorMessage .= ': Template rendering issue. Please check the view file.';
            } else {
                $errorMessage .= ': ' . $e->getMessage();
            }

            throw new \Exception($errorMessage);
        }
    }

    public function download($filename = null)
    {
        if (!$filename) {
            $filename = $this->generateFilename();
        }

        $pdf = $this->generate();
        return $pdf->download($filename);
    }

    public function stream($filename = null)
    {
        if (!$filename) {
            $filename = $this->generateFilename();
        }

        $pdf = $this->generate();
        return $pdf->stream($filename);
    }

    public function save($path)
    {
        $pdf = $this->generate();
        return $pdf->save($path);
    }

    protected function generateFilename()
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filterSuffix = '';

        if (!empty($this->filters['supplier_type'])) {
            $filterSuffix .= '_' . strtolower($this->filters['supplier_type']);
        }

        if (!empty($this->filters['status'])) {
            $filterSuffix .= '_' . strtolower($this->filters['status']);
        }

        return "suppliers_performance_report{$filterSuffix}_{$timestamp}.pdf";
    }

    protected function calculateStatistics()
    {
        if ($this->suppliers->isEmpty()) {
            return [
                'total_suppliers' => 0,
                'active_suppliers' => 0,
                'inactive_suppliers' => 0,
                'total_orders' => 0,
                'total_spent' => 0,
                'average_rating' => 0,
                'total_credit_limit' => 0,
                'top_supplier' => null,
                'performance_distribution' => [],
            ];
        }

        $totalSuppliers = $this->suppliers->count();
        $activeSuppliers = $this->suppliers->where('is_active', true)->count();
        $inactiveSuppliers = $totalSuppliers - $activeSuppliers;
        $totalOrders = $this->suppliers->sum('total_orders');
        $totalSpent = $this->suppliers->sum('total_spent');
        $averageRating = $this->suppliers->avg('rating');
        $totalCreditLimit = $this->suppliers->sum('credit_limit');

        // Find top performing supplier
        $topSupplier = $this->suppliers->sortByDesc('total_spent')->first();

        // Performance distribution
        $performanceDistribution = [
            'excellent' => $this->suppliers->where('rating', '>=', 4.5)->count(),
            'good' => $this->suppliers->whereBetween('rating', [3.5, 4.4])->count(),
            'average' => $this->suppliers->whereBetween('rating', [2.5, 3.4])->count(),
            'poor' => $this->suppliers->where('rating', '<', 2.5)->count(),
        ];

        return [
            'total_suppliers' => $totalSuppliers,
            'active_suppliers' => $activeSuppliers,
            'inactive_suppliers' => $inactiveSuppliers,
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'average_rating' => $averageRating,
            'total_credit_limit' => $totalCreditLimit,
            'top_supplier' => $topSupplier,
            'performance_distribution' => $performanceDistribution,
            'average_orders_per_supplier' => $totalSuppliers > 0 ? $totalOrders / $totalSuppliers : 0,
            'average_spent_per_supplier' => $totalSuppliers > 0 ? $totalSpent / $totalSuppliers : 0,
        ];
    }

    public function getStatistics()
    {
        return $this->calculateStatistics();
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function addFilter($key, $value)
    {
        $this->filters[$key] = $value;
        return $this;
    }

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getSuppliersCount()
    {
        return $this->suppliers->count();
    }

    public function isEmpty()
    {
        return $this->suppliers->isEmpty();
    }

    public function isValid()
    {
        return !$this->isEmpty() && $this->suppliers->count() > 0;
    }

    public static function create($suppliers, $filters = [], $options = [])
    {
        return new static($suppliers, $filters, $options);
    }
}
