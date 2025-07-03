<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Modules\Sales\Models\Sale;
use App\Modules\Sales\Models\SaleItem;
use App\Modules\Customer\Models\Customer;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Category;
use App\Modules\Supplier\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SalesManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'unit_price' => 100,
            'cost_price' => 60,
            'quantity_in_stock' => 50,
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_a_sale()
    {
        $saleData = [
            'customer_id' => $this->customer->id,
            'sale_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'notes' => 'Test sale',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 100,
                    'discount_percentage' => 0,
                ]
            ]
        ];

        $response = $this->postJson('/api/sales', $saleData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'invoice_number',
                        'customer',
                        'total_amount',
                        'payment_status',
                        'items',
                    ]
                ]);

        $this->assertDatabaseHas('sales', [
            'customer_id' => $this->customer->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 100,
        ]);

        // Check inventory was updated
        $this->product->refresh();
        $this->assertEquals(45, $this->product->quantity_in_stock);
    }

    /** @test */
    public function it_calculates_sale_totals_correctly()
    {
        $saleData = [
            'customer_id' => $this->customer->id,
            'sale_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'tax_percentage' => 10,
            'discount_percentage' => 5,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 100,
                    'discount_percentage' => 10, // Item discount
                ],
            ]
        ];

        $response = $this->postJson('/api/sales', $saleData);

        $response->assertStatus(201);

        $sale = Sale::latest()->first();
        
        // Item total: 2 * 100 = 200
        // Item discount: 200 * 10% = 20
        // Item subtotal: 200 - 20 = 180
        // Sale discount: 180 * 5% = 9
        // Subtotal after discount: 180 - 9 = 171
        // Tax: 171 * 10% = 17.1
        // Total: 171 + 17.1 = 188.1

        $this->assertEquals(200, $sale->subtotal);
        $this->assertEquals(29, $sale->total_discount); // 20 + 9
        $this->assertEquals(17.1, $sale->tax_amount);
        $this->assertEquals(188.1, $sale->total_amount);
    }

    /** @test */
    public function it_prevents_overselling_products()
    {
        $this->product->update(['quantity_in_stock' => 3]);

        $saleData = [
            'customer_id' => $this->customer->id,
            'sale_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5, // More than available stock
                    'unit_price' => 100,
                ]
            ]
        ];

        $response = $this->postJson('/api/sales', $saleData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function it_can_process_partial_payments()
    {
        $sale = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'total_amount' => 1000,
            'payment_status' => 'partial',
            'amount_paid' => 600,
        ]);

        $paymentData = [
            'amount' => 200,
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->format('Y-m-d'),
            'reference_number' => 'TXN123456',
        ];

        $response = $this->postJson("/api/sales/{$sale->id}/payments", $paymentData);

        $response->assertStatus(201);

        $sale->refresh();
        $this->assertEquals(800, $sale->amount_paid);
        $this->assertEquals('partial', $sale->payment_status);

        $this->assertDatabaseHas('sale_payments', [
            'sale_id' => $sale->id,
            'amount' => 200,
            'payment_method' => 'bank_transfer',
        ]);
    }

    /** @test */
    public function it_can_generate_sales_reports()
    {
        // Create sales for different dates
        Sale::factory()->count(5)->create([
            'customer_id' => $this->customer->id,
            'sale_date' => now()->subDays(1),
            'total_amount' => 1000,
        ]);

        Sale::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
            'sale_date' => now(),
            'total_amount' => 1500,
        ]);

        $response = $this->getJson('/api/reports/sales?' . http_build_query([
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_sales',
                        'total_amount',
                        'average_sale_amount',
                        'daily_breakdown' => [
                            '*' => [
                                'date',
                                'sales_count',
                                'total_amount',
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_apply_customer_discounts()
    {
        $this->customer->update([
            'discount_percentage' => 15,
            'customer_type' => 'wholesale',
        ]);

        $saleData = [
            'customer_id' => $this->customer->id,
            'sale_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
            'apply_customer_discount' => true,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 100,
                ]
            ]
        ];

        $response = $this->postJson('/api/sales', $saleData);

        $response->assertStatus(201);

        $sale = Sale::latest()->first();
        
        // Subtotal: 2 * 100 = 200
        // Customer discount: 200 * 15% = 30
        // Total: 200 - 30 = 170

        $this->assertEquals(200, $sale->subtotal);
        $this->assertEquals(30, $sale->total_discount);
        $this->assertEquals(170, $sale->total_amount);
    }

    /** @test */
    public function it_can_handle_returns_and_refunds()
    {
        $sale = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'total_amount' => 500,
            'payment_status' => 'paid',
        ]);

        $saleItem = SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 100,
        ]);

        $returnData = [
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 2,
                    'reason' => 'Defective product',
                ]
            ],
            'refund_method' => 'cash',
        ];

        $response = $this->postJson("/api/sales/{$sale->id}/returns", $returnData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'return_number',
                        'total_refund_amount',
                        'items',
                    ]
                ]);

        // Check inventory was restored
        $this->product->refresh();
        $this->assertEquals(52, $this->product->quantity_in_stock); // Original 50 + 2 returned
    }

    /** @test */
    public function it_can_track_sales_performance_by_user()
    {
        $salesUser1 = Sale::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'total_amount' => 1000,
            'sale_date' => now(),
        ]);

        $otherUser = User::factory()->create();
        $salesUser2 = Sale::factory()->count(2)->create([
            'user_id' => $otherUser->id,
            'total_amount' => 1500,
            'sale_date' => now(),
        ]);

        $response = $this->getJson('/api/reports/sales-performance?' . http_build_query([
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'users' => [
                            '*' => [
                                'user_id',
                                'user_name',
                                'sales_count',
                                'total_amount',
                                'average_sale_amount',
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_validates_sale_data()
    {
        $invalidData = [
            'customer_id' => 999, // Non-existent customer
            'sale_date' => 'invalid-date',
            'payment_method' => 'invalid_method',
            'items' => [
                [
                    'product_id' => 999, // Non-existent product
                    'quantity' => -1, // Negative quantity
                    'unit_price' => 'not_a_number',
                ]
            ]
        ];

        $response = $this->postJson('/api/sales', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'customer_id',
                    'sale_date',
                    'payment_method',
                    'items.0.product_id',
                    'items.0.quantity',
                    'items.0.unit_price',
                ]);
    }

    /** @test */
    public function it_can_generate_invoice_pdf()
    {
        $sale = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-2024-001',
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->get("/api/sales/{$sale->id}/invoice/pdf");

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function it_can_send_invoice_via_whatsapp()
    {
        $sale = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-2024-001',
        ]);

        $this->customer->update(['phone' => '+964770123456']);

        $response = $this->postJson("/api/sales/{$sale->id}/send-whatsapp", [
            'include_pdf' => true,
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'whatsapp_message_id',
                ]);
    }
}
