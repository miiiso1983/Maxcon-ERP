<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Category;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Supplier\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Category $category;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->supplier = Supplier::factory()->create();
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_a_product()
    {
        $productData = [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'barcode' => $this->faker->unique()->ean13(),
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'unit_price' => $this->faker->randomFloat(2, 10, 1000),
            'cost_price' => $this->faker->randomFloat(2, 5, 500),
            'quantity_in_stock' => $this->faker->numberBetween(0, 100),
            'minimum_stock_level' => $this->faker->numberBetween(5, 20),
            'maximum_stock_level' => $this->faker->numberBetween(50, 200),
            'unit_of_measure' => $this->faker->randomElement(['piece', 'box', 'kg', 'liter']),
            'is_active' => true,
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'sku',
                        'barcode',
                        'unit_price',
                        'quantity_in_stock',
                        'category',
                        'supplier',
                    ]
                ]);

        $this->assertDatabaseHas('products', [
            'name' => $productData['name'],
            'sku' => $productData['sku'],
            'barcode' => $productData['barcode'],
        ]);
    }

    /** @test */
    public function it_can_update_product_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'quantity_in_stock' => 50,
        ]);

        $stockUpdateData = [
            'quantity' => 25,
            'movement_type' => 'stock_in',
            'reason' => 'Purchase order received',
            'reference_number' => 'PO-2024-001',
        ];

        $response = $this->postJson("/api/products/{$product->id}/stock", $stockUpdateData);

        $response->assertStatus(200);

        $product->refresh();
        $this->assertEquals(75, $product->quantity_in_stock);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'movement_type' => 'stock_in',
            'quantity' => 25,
            'reference_number' => 'PO-2024-001',
        ]);
    }

    /** @test */
    public function it_can_detect_low_stock_products()
    {
        // Create products with different stock levels
        $lowStockProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'quantity_in_stock' => 5,
            'minimum_stock_level' => 10,
        ]);

        $normalStockProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'quantity_in_stock' => 50,
            'minimum_stock_level' => 10,
        ]);

        $response = $this->getJson('/api/products/low-stock');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['id' => $lowStockProduct->id]);
    }

    /** @test */
    public function it_can_generate_inventory_valuation_report()
    {
        $products = Product::factory()->count(5)->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->getJson('/api/reports/inventory-valuation');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_value',
                        'total_cost',
                        'total_products',
                        'categories' => [
                            '*' => [
                                'category_name',
                                'product_count',
                                'total_value',
                                'total_cost',
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_track_inventory_movements()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'quantity_in_stock' => 100,
        ]);

        // Create various inventory movements
        $movements = [
            ['type' => 'stock_in', 'quantity' => 50, 'reason' => 'Purchase'],
            ['type' => 'stock_out', 'quantity' => 20, 'reason' => 'Sale'],
            ['type' => 'adjustment', 'quantity' => -5, 'reason' => 'Damage'],
        ];

        foreach ($movements as $movement) {
            StockMovement::factory()->create([
                'product_id' => $product->id,
                'type' => $movement['type'],
                'quantity' => $movement['quantity'],
                'notes' => $movement['reason'],
                'user_id' => $this->user->id,
            ]);
        }

        $response = $this->getJson("/api/products/{$product->id}/movements");

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'movement_type',
                            'quantity',
                            'reason',
                            'created_at',
                            'user' => ['name'],
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_validates_product_creation_data()
    {
        $invalidData = [
            'name' => '', // Required field
            'sku' => 'INVALID SKU WITH SPACES',
            'unit_price' => -10, // Negative price
            'quantity_in_stock' => 'not_a_number',
        ];

        $response = $this->postJson('/api/products', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'sku', 'unit_price', 'quantity_in_stock']);
    }

    /** @test */
    public function it_prevents_duplicate_sku_creation()
    {
        $existingProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'UNIQUE123',
        ]);

        $duplicateData = [
            'name' => 'New Product',
            'sku' => 'UNIQUE123', // Duplicate SKU
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'unit_price' => 100,
            'cost_price' => 50,
            'quantity_in_stock' => 10,
        ];

        $response = $this->postJson('/api/products', $duplicateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['sku']);
    }

    /** @test */
    public function it_can_search_products_by_multiple_criteria()
    {
        $searchableProduct = Product::factory()->create([
            'name' => 'Paracetamol 500mg Tablets',
            'sku' => 'PARA500',
            'barcode' => '1234567890123',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $otherProduct = Product::factory()->create([
            'name' => 'Ibuprofen 400mg Capsules',
            'sku' => 'IBU400',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        // Search by name
        $response = $this->getJson('/api/products/search?q=Paracetamol');
        $response->assertStatus(200)
                ->assertJsonFragment(['id' => $searchableProduct->id]);

        // Search by SKU
        $response = $this->getJson('/api/products/search?q=PARA500');
        $response->assertStatus(200)
                ->assertJsonFragment(['id' => $searchableProduct->id]);

        // Search by barcode
        $response = $this->getJson('/api/products/search?q=1234567890123');
        $response->assertStatus(200)
                ->assertJsonFragment(['id' => $searchableProduct->id]);
    }

    /** @test */
    public function it_can_calculate_product_profitability()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'unit_price' => 100,
            'cost_price' => 60,
        ]);

        $response = $this->getJson("/api/products/{$product->id}/profitability");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'unit_price',
                        'cost_price',
                        'profit_margin',
                        'profit_percentage',
                        'markup_percentage',
                    ]
                ]);

        $data = $response->json('data');
        $this->assertEquals(40, $data['profit_margin']); // 100 - 60
        $this->assertEquals(40, $data['profit_percentage']); // (40/100) * 100
        $this->assertEquals(66.67, round($data['markup_percentage'], 2)); // (40/60) * 100
    }

    /** @test */
    public function it_can_handle_bulk_stock_updates()
    {
        $products = Product::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $bulkUpdateData = [
            'updates' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 50,
                    'movement_type' => 'stock_in',
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 30,
                    'movement_type' => 'stock_in',
                ],
                [
                    'product_id' => $products[2]->id,
                    'quantity' => 20,
                    'movement_type' => 'adjustment',
                ],
            ],
            'reason' => 'Bulk inventory update',
            'reference_number' => 'BULK-2024-001',
        ];

        $response = $this->postJson('/api/products/bulk-stock-update', $bulkUpdateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'updated_count',
                        'failed_count',
                        'results',
                    ]
                ]);

        // Verify inventory movements were created
        $this->assertDatabaseCount('inventory_movements', 3);
    }

    /** @test */
    public function it_can_export_inventory_data()
    {
        Product::factory()->count(10)->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->getJson('/api/products/export?format=csv');

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
                ->assertHeader('Content-Disposition', 'attachment; filename="products_export.csv"');
    }
}
