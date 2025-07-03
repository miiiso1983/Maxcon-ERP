<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Customer\Models\Customer;
use App\Modules\Sales\Models\Sale;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Models\User;

class SalesCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample customers
        $customers = [
            [
                'name' => ['en' => 'Ahmed Ali Hassan', 'ar' => 'أحمد علي حسن', 'ku' => 'ئەحمەد عەلی حەسەن'],
                'email' => 'ahmed.ali@example.com',
                'phone' => '+964-770-123-4567',
                'mobile' => '+964-750-123-4567',
                'address' => ['en' => 'Baghdad, Al-Karrada', 'ar' => 'بغداد، الكرادة', 'ku' => 'بەغدا، کەڕادە'],
                'city' => 'Baghdad',
                'country' => 'Iraq',
                'customer_type' => 'individual',
                'credit_limit' => 1000.00,
                'payment_terms' => 30,
                'discount_percentage' => 5.00,
                'gender' => 'male',
            ],
            [
                'name' => ['en' => 'Sara Mohammed', 'ar' => 'سارة محمد', 'ku' => 'سارا محەمەد'],
                'email' => 'sara.mohammed@example.com',
                'phone' => '+964-771-234-5678',
                'address' => ['en' => 'Erbil, Downtown', 'ar' => 'أربيل، وسط المدينة', 'ku' => 'هەولێر، ناوەندی شار'],
                'city' => 'Erbil',
                'country' => 'Iraq',
                'customer_type' => 'individual',
                'credit_limit' => 500.00,
                'payment_terms' => 15,
                'gender' => 'female',
            ],
            [
                'name' => ['en' => 'Al-Noor Medical Center', 'ar' => 'مركز النور الطبي', 'ku' => 'سەنتەری پزیشکی نوور'],
                'email' => 'info@alnoor-medical.com',
                'phone' => '+964-770-345-6789',
                'address' => ['en' => 'Baghdad, Al-Mansour', 'ar' => 'بغداد، المنصور', 'ku' => 'بەغدا، مەنسوور'],
                'city' => 'Baghdad',
                'country' => 'Iraq',
                'customer_type' => 'hospital',
                'credit_limit' => 10000.00,
                'payment_terms' => 60,
                'discount_percentage' => 15.00,
            ],
            [
                'name' => ['en' => 'Green Pharmacy', 'ar' => 'صيدلية الأخضر', 'ku' => 'دەرمانخانەی سەوز'],
                'email' => 'contact@green-pharmacy.com',
                'phone' => '+964-772-456-7890',
                'address' => ['en' => 'Basra, City Center', 'ar' => 'البصرة، مركز المدينة', 'ku' => 'بەسرە، ناوەندی شار'],
                'city' => 'Basra',
                'country' => 'Iraq',
                'customer_type' => 'pharmacy',
                'credit_limit' => 5000.00,
                'payment_terms' => 45,
                'discount_percentage' => 10.00,
            ],
            [
                'name' => ['en' => 'Omar Hassan Clinic', 'ar' => 'عيادة عمر حسن', 'ku' => 'نەخۆشخانەی عومەر حەسەن'],
                'email' => 'dr.omar@clinic.com',
                'phone' => '+964-773-567-8901',
                'address' => ['en' => 'Sulaymaniyah, Medical District', 'ar' => 'السليمانية، المنطقة الطبية', 'ku' => 'سلێمانی، ناوچەی پزیشکی'],
                'city' => 'Sulaymaniyah',
                'country' => 'Iraq',
                'customer_type' => 'clinic',
                'credit_limit' => 3000.00,
                'payment_terms' => 30,
                'discount_percentage' => 8.00,
            ],
        ];

        foreach ($customers as $index => $customerData) {
            $customerData['customer_code'] = 'TEMP-' . ($index + 1);
            $customer = Customer::create($customerData);
            $customer->customer_code = $customer->generateCustomerCode();
            $customer->save();
        }

        // Create sample sales
        $user = User::first();
        $warehouse = Warehouse::first();
        $products = Product::take(2)->get();
        $customers = Customer::take(3)->get();

        if ($user && $warehouse && $products->count() > 0 && $customers->count() > 0) {
            foreach ($customers as $customer) {
                // Create 2-3 sales per customer
                for ($i = 0; $i < rand(2, 3); $i++) {
                    $sale = Sale::create([
                        'invoice_number' => 'TEMP-' . time() . '-' . rand(1000, 9999),
                        'customer_id' => $customer->id,
                        'user_id' => $user->id,
                        'warehouse_id' => $warehouse->id,
                        'sale_date' => now()->subDays(rand(1, 30)),
                        'status' => Sale::STATUS_CONFIRMED,
                        'payment_method' => collect(['cash', 'card', 'transfer'])->random(),
                    ]);

                    $sale->invoice_number = $sale->generateInvoiceNumber();
                    $sale->save();

                    // Add 1-3 items per sale
                    foreach ($products->random(rand(1, 2)) as $product) {
                        $quantity = rand(1, 5);
                        $unitPrice = $product->selling_price * (1 + (rand(-10, 20) / 100)); // ±20% price variation

                        $totalAmount = $quantity * $unitPrice;
                        $saleItem = $sale->items()->create([
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'product_sku' => $product->sku,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'cost_price' => $product->cost_price,
                            'tax_rate' => $product->tax_rate,
                            'total_amount' => $totalAmount,
                        ]);

                        $saleItem->calculateTotals();
                        $saleItem->save();
                    }

                    // Calculate sale totals
                    $sale->calculateTotals();
                    $sale->save();

                    // Add payment (80% chance of full payment)
                    if (rand(1, 100) <= 80) {
                        $sale->addPayment(
                            $sale->total_amount,
                            $sale->payment_method,
                            "Payment for invoice {$sale->invoice_number}"
                        );
                    } else {
                        // Partial payment
                        $partialAmount = $sale->total_amount * (rand(30, 70) / 100);
                        $sale->addPayment(
                            $partialAmount,
                            $sale->payment_method,
                            "Partial payment for invoice {$sale->invoice_number}"
                        );
                    }

                    // Add loyalty points
                    $customer->addLoyaltyPoints($sale->total_amount);
                }
            }
        }

        $this->command->info('Sales and Customer data seeded successfully!');
    }
}
