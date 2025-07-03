<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    private $options;
    private $results;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'skip_duplicates' => true,
            'update_existing' => false,
            'validate_only' => false,
            'default_category' => null,
            'default_unit' => 'piece',
            'default_active' => true,
        ], $options);

        $this->results = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
            'error_details' => [],
            'valid_rows' => 0,
            'invalid_rows' => 0,
        ];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because of 0-based index and header row
            
            try {
                $this->processRow($row->toArray(), $rowNumber);
            } catch (\Exception $e) {
                $this->results['errors']++;
                $this->results['error_details'][] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray()
                ];
            }
        }
    }

    private function processRow(array $row, int $rowNumber)
    {
        // Clean and prepare data
        $data = $this->prepareRowData($row);
        
        // Validate row data
        $validator = $this->validateRowData($data, $rowNumber);
        
        if ($validator->fails()) {
            $this->results['errors']++;
            $this->results['invalid_rows']++;
            $this->results['error_details'][] = [
                'row' => $rowNumber,
                'error' => 'Validation failed: ' . implode(', ', $validator->errors()->all()),
                'data' => $data
            ];
            return;
        }

        $this->results['valid_rows']++;

        // If validation only, don't actually import
        if ($this->options['validate_only']) {
            return;
        }

        // Check if product exists
        $existingProduct = $this->findExistingProduct($data['sku']);

        if ($existingProduct) {
            if ($this->options['skip_duplicates']) {
                $this->results['skipped']++;
                return;
            } elseif ($this->options['update_existing']) {
                $this->updateProduct($existingProduct, $data);
                $this->results['imported']++;
                return;
            } else {
                $this->results['errors']++;
                $this->results['error_details'][] = [
                    'row' => $rowNumber,
                    'error' => 'Product with SKU already exists: ' . $data['sku'],
                    'data' => $data
                ];
                return;
            }
        }

        // Create new product
        $this->createProduct($data);
        $this->results['imported']++;
    }

    private function prepareRowData(array $row): array
    {
        // Map Excel columns to database fields
        $data = [
            'name' => trim($row['name'] ?? ''),
            'sku' => strtoupper(trim($row['sku'] ?? '')),
            'barcode' => trim($row['barcode'] ?? ''),
            'description' => trim($row['description'] ?? ''),
            'selling_price' => $this->parseNumeric($row['selling_price'] ?? 0),
            'cost_price' => $this->parseNumeric($row['cost_price'] ?? 0),
            'quantity' => $this->parseNumeric($row['quantity'] ?? 0, 0),
            'min_quantity' => $this->parseNumeric($row['min_quantity'] ?? 0, 0),
            'unit' => strtolower(trim($row['unit'] ?? $this->options['default_unit'])),
            'category' => trim($row['category'] ?? $this->options['default_category']),
            'tax_rate' => $this->parseNumeric($row['tax_rate'] ?? 0),
            'is_active' => $this->parseBoolean($row['is_active'] ?? $this->options['default_active']),
            'track_quantity' => $this->parseBoolean($row['track_quantity'] ?? true),
        ];

        // Generate SKU if empty
        if (empty($data['sku']) && !empty($data['name'])) {
            $data['sku'] = $this->generateSku($data['name']);
        }

        return $data;
    }

    private function validateRowData(array $data, int $rowNumber): \Illuminate\Validation\Validator
    {
        $rules = [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|in:piece,box,bottle,vial,pack',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ];

        $messages = [
            'name.required' => 'Product name is required',
            'sku.required' => 'SKU is required',
            'selling_price.required' => 'Selling price is required',
            'selling_price.numeric' => 'Selling price must be a number',
            'selling_price.min' => 'Selling price must be greater than or equal to 0',
        ];

        return Validator::make($data, $rules, $messages);
    }

    private function findExistingProduct(string $sku)
    {
        // In a real implementation, this would query the database
        // For demo purposes, return null (no existing products)
        return null;
        
        // Example implementation:
        // return Product::where('sku', $sku)->first();
    }

    private function createProduct(array $data)
    {
        // In a real implementation, this would create a product in the database
        // For demo purposes, just log the action
        
        // Example implementation:
        // Product::create($data);
        
        \Log::info('Creating product: ' . $data['name'] . ' (SKU: ' . $data['sku'] . ')');
    }

    private function updateProduct($product, array $data)
    {
        // In a real implementation, this would update the existing product
        // For demo purposes, just log the action
        
        // Example implementation:
        // $product->update($data);
        
        \Log::info('Updating product: ' . $data['name'] . ' (SKU: ' . $data['sku'] . ')');
    }

    private function parseNumeric($value, int $decimals = 2): float
    {
        if (is_numeric($value)) {
            return round((float) $value, $decimals);
        }
        
        // Try to clean the value (remove currency symbols, etc.)
        $cleaned = preg_replace('/[^\d.,]/', '', (string) $value);
        $cleaned = str_replace(',', '.', $cleaned);
        
        return is_numeric($cleaned) ? round((float) $cleaned, $decimals) : 0;
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes', 'y', 'active', 'enabled']);
    }

    private function generateSku(string $name): string
    {
        // Generate SKU from product name
        $sku = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $sku = substr($sku, 0, 8);
        
        // Add random suffix to ensure uniqueness
        $sku .= str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $sku;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
