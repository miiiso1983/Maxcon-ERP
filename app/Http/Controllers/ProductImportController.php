<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Exports\ProductTemplateExport;

class ProductImportController extends Controller
{
    /**
     * Show the import form
     */
    public function showImportForm()
    {
        return view('tenant.inventory.products.import');
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        return Excel::download(new ProductTemplateExport, 'products_template.xlsx');
    }

    /**
     * Import products from Excel
     */
    public function import(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean',
            'validate_only' => 'boolean',
            'default_category' => 'nullable|string',
            'default_unit' => 'nullable|string',
            'default_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('excel_file');
            
            // Create import instance with options
            $import = new ProductsImport([
                'skip_duplicates' => $request->boolean('skip_duplicates'),
                'update_existing' => $request->boolean('update_existing'),
                'validate_only' => $request->boolean('validate_only'),
                'default_category' => $request->input('default_category'),
                'default_unit' => $request->input('default_unit'),
                'default_active' => $request->boolean('default_active', true),
            ]);

            // Import the file
            Excel::import($import, $file);

            // Get import results
            $results = $import->getResults();

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'imported' => $results['imported'],
                'skipped' => $results['skipped'],
                'errors' => $results['errors'],
                'error_details' => $results['error_details']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate Excel file without importing
     */
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('excel_file');
            
            // Create import instance for validation only
            $import = new ProductsImport([
                'validate_only' => true,
            ]);

            Excel::import($import, $file);
            $results = $import->getResults();

            return response()->json([
                'success' => true,
                'valid_rows' => $results['valid_rows'],
                'invalid_rows' => $results['invalid_rows'],
                'errors' => $results['error_details']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get import progress (for real-time updates)
     */
    public function getProgress(Request $request)
    {
        $sessionId = $request->input('session_id');
        
        // In a real implementation, you would store progress in cache/database
        // and retrieve it here. For demo purposes, return mock data.
        
        return response()->json([
            'progress' => rand(10, 90),
            'status' => 'processing',
            'message' => 'Processing row ' . rand(1, 100) . ' of 100'
        ]);
    }
}
