<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Inventory\Models\Category;
use App\Modules\Inventory\Models\Brand;
use App\Modules\Inventory\Models\Unit;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\Product;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Units
        $units = [
            ['name' => ['en' => 'Piece', 'ar' => 'قطعة', 'ku' => 'دانە'], 'short_name' => ['en' => 'pcs', 'ar' => 'قطعة', 'ku' => 'دانە'], 'type' => 'piece'],
            ['name' => ['en' => 'Box', 'ar' => 'صندوق', 'ku' => 'سندوق'], 'short_name' => ['en' => 'box', 'ar' => 'صندوق', 'ku' => 'سندوق'], 'type' => 'piece'],
            ['name' => ['en' => 'Kilogram', 'ar' => 'كيلوغرام', 'ku' => 'کیلۆگرام'], 'short_name' => ['en' => 'kg', 'ar' => 'كغ', 'ku' => 'کگ'], 'type' => 'weight'],
            ['name' => ['en' => 'Gram', 'ar' => 'غرام', 'ku' => 'گرام'], 'short_name' => ['en' => 'g', 'ar' => 'غ', 'ku' => 'گ'], 'type' => 'weight'],
            ['name' => ['en' => 'Liter', 'ar' => 'لتر', 'ku' => 'لیتر'], 'short_name' => ['en' => 'L', 'ar' => 'ل', 'ku' => 'ل'], 'type' => 'volume'],
            ['name' => ['en' => 'Milliliter', 'ar' => 'مليلتر', 'ku' => 'میلیلیتر'], 'short_name' => ['en' => 'ml', 'ar' => 'مل', 'ku' => 'مل'], 'type' => 'volume'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }

        // Create Categories
        $categories = [
            ['name' => ['en' => 'Medicines', 'ar' => 'الأدوية', 'ku' => 'دەرمان'], 'code' => 'MED', 'description' => ['en' => 'All types of medicines', 'ar' => 'جميع أنواع الأدوية', 'ku' => 'هەموو جۆرەکانی دەرمان']],
            ['name' => ['en' => 'Antibiotics', 'ar' => 'المضادات الحيوية', 'ku' => 'دژەمیکرۆب'], 'code' => 'ANT', 'description' => ['en' => 'Antibiotic medications', 'ar' => 'الأدوية المضادة للبكتيريا', 'ku' => 'دەرمانی دژەمیکرۆب']],
            ['name' => ['en' => 'Pain Relief', 'ar' => 'مسكنات الألم', 'ku' => 'ئازارکوژ'], 'code' => 'PAIN', 'description' => ['en' => 'Pain relief medications', 'ar' => 'أدوية تسكين الألم', 'ku' => 'دەرمانی ئازارکوژ']],
            ['name' => ['en' => 'Vitamins', 'ar' => 'الفيتامينات', 'ku' => 'ڤیتامین'], 'code' => 'VIT', 'description' => ['en' => 'Vitamin supplements', 'ar' => 'المكملات الغذائية', 'ku' => 'پێکهاتەی ڤیتامین']],
            ['name' => ['en' => 'Medical Supplies', 'ar' => 'المستلزمات الطبية', 'ku' => 'پێداویستییە پزیشکییەکان'], 'code' => 'SUP', 'description' => ['en' => 'Medical supplies and equipment', 'ar' => 'المستلزمات والمعدات الطبية', 'ku' => 'پێداویستی و ئامێری پزیشکی']],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create Brands
        $brands = [
            ['name' => ['en' => 'Pfizer', 'ar' => 'فايزر', 'ku' => 'فایزەر'], 'code' => 'PFZ', 'description' => ['en' => 'Global pharmaceutical company', 'ar' => 'شركة أدوية عالمية', 'ku' => 'کۆمپانیای دەرمانی جیهانی']],
            ['name' => ['en' => 'Johnson & Johnson', 'ar' => 'جونسون آند جونسون', 'ku' => 'جۆنسۆن و جۆنسۆن'], 'code' => 'JNJ', 'description' => ['en' => 'Healthcare products company', 'ar' => 'شركة منتجات الرعاية الصحية', 'ku' => 'کۆمپانیای بەرهەمی تەندروستی']],
            ['name' => ['en' => 'Novartis', 'ar' => 'نوفارتيس', 'ku' => 'نۆڤارتیس'], 'code' => 'NVS', 'description' => ['en' => 'Swiss pharmaceutical company', 'ar' => 'شركة أدوية سويسرية', 'ku' => 'کۆمپانیای دەرمانی سویسری']],
            ['name' => ['en' => 'Roche', 'ar' => 'روش', 'ku' => 'ڕۆش'], 'code' => 'ROG', 'description' => ['en' => 'Swiss healthcare company', 'ar' => 'شركة رعاية صحية سويسرية', 'ku' => 'کۆمپانیای تەندروستی سویسری']],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        // Create Warehouses
        $warehouses = [
            [
                'name' => ['en' => 'Main Warehouse', 'ar' => 'المستودع الرئيسي', 'ku' => 'کۆگای سەرەکی'],
                'code' => 'MAIN',
                'description' => ['en' => 'Primary storage facility', 'ar' => 'مرفق التخزين الأساسي', 'ku' => 'شوێنی هەڵگرتنی سەرەکی'],
                'address' => 'Baghdad, Iraq',
                'city' => 'Baghdad',
                'country' => 'Iraq',
                'is_default' => true,
                'capacity' => 10000,
                'type' => 'main'
            ],
            [
                'name' => ['en' => 'Branch Warehouse', 'ar' => 'مستودع الفرع', 'ku' => 'کۆگای لق'],
                'code' => 'BRANCH',
                'description' => ['en' => 'Secondary storage facility', 'ar' => 'مرفق التخزين الثانوي', 'ku' => 'شوێنی هەڵگرتنی لاوەکی'],
                'address' => 'Erbil, Iraq',
                'city' => 'Erbil',
                'country' => 'Iraq',
                'capacity' => 5000,
                'type' => 'branch'
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }

        // Create Sample Products
        $products = [
            [
                'name' => ['en' => 'Paracetamol 500mg', 'ar' => 'باراسيتامول 500 ملغ', 'ku' => 'پاراسیتامۆل ٥٠٠ میلیگرام'],
                'description' => ['en' => 'Pain relief and fever reducer', 'ar' => 'مسكن للألم وخافض للحرارة', 'ku' => 'ئازارکوژ و خوارخەرکەر'],
                'sku' => 'MED-000001',
                'barcode' => '1234567890123',
                'category_id' => 3, // Pain Relief
                'brand_id' => 1, // Pfizer
                'unit_id' => 1, // Piece
                'cost_price' => 0.50,
                'selling_price' => 1.00,
                'min_stock_level' => 100,
                'max_stock_level' => 1000,
                'reorder_level' => 200,
                'has_expiry' => true,
                'has_batch' => true,
            ],
            [
                'name' => ['en' => 'Amoxicillin 250mg', 'ar' => 'أموكسيسيلين 250 ملغ', 'ku' => 'ئامۆکسیسیلین ٢٥٠ میلیگرام'],
                'description' => ['en' => 'Antibiotic medication', 'ar' => 'دواء مضاد حيوي', 'ku' => 'دەرمانی دژەمیکرۆب'],
                'sku' => 'MED-000002',
                'barcode' => '1234567890124',
                'category_id' => 2, // Antibiotics
                'brand_id' => 2, // Johnson & Johnson
                'unit_id' => 1, // Piece
                'cost_price' => 1.20,
                'selling_price' => 2.50,
                'min_stock_level' => 50,
                'max_stock_level' => 500,
                'reorder_level' => 100,
                'has_expiry' => true,
                'has_batch' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('Inventory data seeded successfully!');
    }
}
