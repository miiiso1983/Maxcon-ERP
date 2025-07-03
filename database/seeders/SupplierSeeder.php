<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Supplier\Models\Supplier;
use App\Modules\Supplier\Models\PurchaseOrder;
use App\Modules\Supplier\Models\SupplierEvaluation;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Models\User;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample suppliers
        $suppliers = [
            [
                'name' => ['en' => 'Al-Shifa Pharmaceuticals', 'ar' => 'شركة الشفاء للأدوية', 'ku' => 'کۆمپانیای شیفا بۆ دەرمان'],
                'company_name' => 'Al-Shifa Pharmaceuticals Ltd.',
                'email' => 'info@alshifa-pharma.com',
                'phone' => '+964-770-111-2222',
                'mobile' => '+964-750-111-2222',
                'address' => ['en' => 'Industrial Zone, Baghdad', 'ar' => 'المنطقة الصناعية، بغداد', 'ku' => 'ناوچەی پیشەسازی، بەغدا'],
                'city' => 'Baghdad',
                'country' => 'Iraq',
                'supplier_type' => 'manufacturer',
                'payment_terms' => 45,
                'credit_limit' => 50000.00,
                'discount_percentage' => 12.00,
                'contact_person' => 'Dr. Ahmed Al-Rashid',
                'website' => 'https://alshifa-pharma.com',
                'supplier_code' => 'TEMP-1',
                'rating' => 4.5,
            ],
            [
                'name' => ['en' => 'Kurdistan Medical Supplies', 'ar' => 'إمدادات كردستان الطبية', 'ku' => 'پێداویستییە پزیشکییەکانی کوردستان'],
                'company_name' => 'Kurdistan Medical Supplies Co.',
                'email' => 'sales@kms-medical.com',
                'phone' => '+964-771-333-4444',
                'address' => ['en' => 'Medical District, Erbil', 'ar' => 'المنطقة الطبية، أربيل', 'ku' => 'ناوچەی پزیشکی، هەولێر'],
                'city' => 'Erbil',
                'country' => 'Iraq',
                'supplier_type' => 'distributor',
                'payment_terms' => 30,
                'credit_limit' => 30000.00,
                'discount_percentage' => 8.00,
                'contact_person' => 'Saman Mahmoud',
                'website' => 'https://kms-medical.com',
                'supplier_code' => 'TEMP-2',
                'rating' => 4.2,
            ],
            [
                'name' => ['en' => 'Babylon Pharma Import', 'ar' => 'بابل فارما للاستيراد', 'ku' => 'بابل فارما بۆ هاوردە'],
                'company_name' => 'Babylon Pharma Import LLC',
                'email' => 'import@babylon-pharma.com',
                'phone' => '+964-772-555-6666',
                'address' => ['en' => 'Port Area, Basra', 'ar' => 'منطقة الميناء، البصرة', 'ku' => 'ناوچەی بەندەر، بەسرە'],
                'city' => 'Basra',
                'country' => 'Iraq',
                'supplier_type' => 'importer',
                'payment_terms' => 60,
                'credit_limit' => 75000.00,
                'discount_percentage' => 15.00,
                'contact_person' => 'Hassan Al-Basri',
                'website' => 'https://babylon-pharma.com',
                'supplier_code' => 'TEMP-3',
                'rating' => 3.8,
            ],
            [
                'name' => ['en' => 'Tigris Medical Equipment', 'ar' => 'معدات دجلة الطبية', 'ku' => 'ئامێرە پزیشکییەکانی دجلە'],
                'company_name' => 'Tigris Medical Equipment Ltd.',
                'email' => 'equipment@tigris-medical.com',
                'phone' => '+964-773-777-8888',
                'address' => ['en' => 'Technology Park, Baghdad', 'ar' => 'حديقة التكنولوجيا، بغداد', 'ku' => 'پارکی تەکنەلۆژیا، بەغدا'],
                'city' => 'Baghdad',
                'country' => 'Iraq',
                'supplier_type' => 'wholesaler',
                'payment_terms' => 30,
                'credit_limit' => 40000.00,
                'discount_percentage' => 10.00,
                'contact_person' => 'Layla Mahmoud',
                'website' => 'https://tigris-medical.com',
                'supplier_code' => 'TEMP-4',
                'rating' => 4.0,
            ],
            [
                'name' => ['en' => 'International Pharma Solutions', 'ar' => 'الحلول الدوائية الدولية', 'ku' => 'چارەسەرە دەرمانییە نێودەوڵەتییەکان'],
                'company_name' => 'International Pharma Solutions Inc.',
                'email' => 'global@ips-pharma.com',
                'phone' => '+964-774-999-0000',
                'address' => ['en' => 'Free Zone, Sulaymaniyah', 'ar' => 'المنطقة الحرة، السليمانية', 'ku' => 'ناوچەی ئازاد، سلێمانی'],
                'city' => 'Sulaymaniyah',
                'country' => 'Iraq',
                'supplier_type' => 'international',
                'payment_terms' => 90,
                'credit_limit' => 100000.00,
                'discount_percentage' => 20.00,
                'contact_person' => 'Omar Jalal',
                'website' => 'https://ips-pharma.com',
                'supplier_code' => 'TEMP-5',
                'rating' => 4.7,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            $supplier = Supplier::create($supplierData);
            $supplier->supplier_code = $supplier->generateSupplierCode();
            $supplier->save();

            // Create evaluations for each supplier
            $user = User::first();
            if ($user) {
                for ($i = 0; $i < rand(2, 4); $i++) {
                    SupplierEvaluation::create([
                        'supplier_id' => $supplier->id,
                        'user_id' => $user->id,
                        'evaluation_date' => now()->subDays(rand(30, 365)),
                        'quality_rating' => rand(35, 50) / 10,
                        'delivery_rating' => rand(35, 50) / 10,
                        'service_rating' => rand(35, 50) / 10,
                        'price_rating' => rand(35, 50) / 10,
                        'communication_rating' => rand(35, 50) / 10,
                        'comments' => 'Sample evaluation comment for ' . $supplier->name,
                        'evaluation_period_start' => now()->subDays(rand(60, 90)),
                        'evaluation_period_end' => now()->subDays(rand(1, 30)),
                    ]);
                }
            }

            // Link suppliers to products
            $products = Product::take(3)->get();
            foreach ($products as $product) {
                $supplier->products()->attach($product->id, [
                    'supplier_sku' => 'SUP-' . $supplier->id . '-' . $product->id,
                    'cost_price' => $product->cost_price * (1 + (rand(-20, 10) / 100)),
                    'lead_time_days' => rand(7, 30),
                    'minimum_order_quantity' => rand(10, 100),
                ]);
            }
        }

        $this->command->info('Supplier data seeded successfully!');
    }
}
