<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Compliance\Models\ComplianceItem;
use App\Modules\Compliance\Models\Inspection;
use App\Modules\Compliance\Models\ComplianceViolation;
use App\Models\User;
use Carbon\Carbon;

class ComplianceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users for assignment
        $users = User::take(5)->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please create users first.');
            return;
        }

        // Create Compliance Items
        $complianceItems = [
            [
                'title' => ['en' => 'Business License', 'ar' => 'رخصة تجارية', 'ku' => 'مۆڵەتی بازرگانی'],
                'description' => ['en' => 'General business operating license from Ministry of Trade', 'ar' => 'رخصة تشغيل الأعمال العامة من وزارة التجارة', 'ku' => 'مۆڵەتی کارکردنی بازرگانی گشتی لە وەزارەتی بازرگانییەوە'],
                'compliance_type' => 'license',
                'category' => 'business',
                'regulatory_body' => 'Ministry of Trade - Iraq',
                'reference_number' => 'BL-2024-001234',
                'issue_date' => now()->subMonths(6),
                'expiry_date' => now()->addMonths(18),
                'priority' => 'critical',
                'risk_level' => 'high',
                'responsible_person_id' => $users[0]->id,
                'cost' => 500000,
                'currency' => 'IQD',
                'requirements' => ['Business registration certificate', 'Tax clearance', 'Location permit'],
                'reminder_days' => 60,
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'title' => ['en' => 'Pharmaceutical License', 'ar' => 'رخصة صيدلانية', 'ku' => 'مۆڵەتی دەرمانسازی'],
                'description' => ['en' => 'License to import and distribute pharmaceutical products', 'ar' => 'رخصة استيراد وتوزيع المنتجات الصيدلانية', 'ku' => 'مۆڵەت بۆ هاوردە و دابەشکردنی بەرهەمە دەرمانییەکان'],
                'compliance_type' => 'license',
                'category' => 'pharmaceutical',
                'regulatory_body' => 'Ministry of Health - Iraq',
                'reference_number' => 'PH-2024-005678',
                'issue_date' => now()->subMonths(3),
                'expiry_date' => now()->addMonths(9),
                'priority' => 'critical',
                'risk_level' => 'very_high',
                'responsible_person_id' => $users->count() > 1 ? $users[1]->id : $users[0]->id,
                'cost' => 2000000,
                'currency' => 'IQD',
                'requirements' => ['Pharmacist certification', 'Storage facility inspection', 'Quality assurance documentation'],
                'reminder_days' => 90,
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'title' => ['en' => 'Environmental Permit', 'ar' => 'تصريح بيئي', 'ku' => 'مۆڵەتی ژینگەیی'],
                'description' => ['en' => 'Environmental impact assessment and operating permit', 'ar' => 'تقييم الأثر البيئي وتصريح التشغيل', 'ku' => 'هەڵسەنگاندنی کاریگەری ژینگەیی و مۆڵەتی کارکردن'],
                'compliance_type' => 'permit',
                'category' => 'environmental',
                'regulatory_body' => 'Ministry of Environment - Iraq',
                'reference_number' => 'ENV-2024-009876',
                'issue_date' => now()->subMonths(12),
                'expiry_date' => now()->addMonths(12),
                'priority' => 'high',
                'risk_level' => 'medium',
                'responsible_person_id' => $users->count() > 2 ? $users[2]->id : $users[0]->id,
                'cost' => 750000,
                'currency' => 'IQD',
                'requirements' => ['Environmental impact study', 'Waste management plan', 'Emission monitoring'],
                'reminder_days' => 45,
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'title' => ['en' => 'Fire Safety Certificate', 'ar' => 'شهادة السلامة من الحريق', 'ku' => 'بڕوانامەی سەلامەتی ئاگر'],
                'description' => ['en' => 'Fire safety compliance certificate for facilities', 'ar' => 'شهادة امتثال السلامة من الحريق للمرافق', 'ku' => 'بڕوانامەی گونجاندنی سەلامەتی ئاگر بۆ دامەزراوەکان'],
                'compliance_type' => 'certification',
                'category' => 'safety',
                'regulatory_body' => 'Civil Defense Directorate - Iraq',
                'reference_number' => 'FS-2024-112233',
                'issue_date' => now()->subMonths(8),
                'expiry_date' => now()->addMonths(4),
                'priority' => 'high',
                'risk_level' => 'high',
                'responsible_person_id' => $users->count() > 3 ? $users[3]->id : $users[0]->id,
                'cost' => 300000,
                'currency' => 'IQD',
                'requirements' => ['Fire safety inspection', 'Emergency evacuation plan', 'Fire suppression systems'],
                'reminder_days' => 30,
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'title' => ['en' => 'Quality Management ISO 9001', 'ar' => 'إدارة الجودة ISO 9001', 'ku' => 'بەڕێوەبردنی کوالیتی ISO 9001'],
                'description' => ['en' => 'ISO 9001:2015 Quality Management System certification', 'ar' => 'شهادة نظام إدارة الجودة ISO 9001:2015', 'ku' => 'بڕوانامەی سیستەمی بەڕێوەبردنی کوالیتی ISO 9001:2015'],
                'compliance_type' => 'certification',
                'category' => 'quality',
                'regulatory_body' => 'Iraqi Organization for Standardization and Quality Control',
                'reference_number' => 'ISO-2024-445566',
                'issue_date' => now()->subMonths(18),
                'expiry_date' => now()->addMonths(6),
                'priority' => 'medium',
                'risk_level' => 'medium',
                'responsible_person_id' => $users->count() > 4 ? $users[4]->id : $users[0]->id,
                'cost' => 1500000,
                'currency' => 'IQD',
                'requirements' => ['Quality manual', 'Process documentation', 'Internal audit reports'],
                'reminder_days' => 60,
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'title' => ['en' => 'Tax Registration Certificate', 'ar' => 'شهادة التسجيل الضريبي', 'ku' => 'بڕوانامەی تۆمارکردنی باج'],
                'description' => ['en' => 'Tax registration and compliance certificate', 'ar' => 'شهادة التسجيل والامتثال الضريبي', 'ku' => 'بڕوانامەی تۆمارکردن و گونجاندنی باج'],
                'compliance_type' => 'registration',
                'category' => 'financial',
                'regulatory_body' => 'General Commission for Taxes - Iraq',
                'reference_number' => 'TAX-2024-778899',
                'issue_date' => now()->subDays(15),
                'expiry_date' => now()->addDays(10), // Expiring soon
                'priority' => 'critical',
                'risk_level' => 'high',
                'responsible_person_id' => $users[0]->id,
                'cost' => 200000,
                'currency' => 'IQD',
                'requirements' => ['Tax returns', 'Financial statements', 'Payment receipts'],
                'reminder_days' => 15,
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'title' => ['en' => 'Expired Import License', 'ar' => 'رخصة استيراد منتهية الصلاحية', 'ku' => 'مۆڵەتی هاوردەی بەسەرچووی ماوە'],
                'description' => ['en' => 'Import license that has expired and needs renewal', 'ar' => 'رخصة استيراد منتهية الصلاحية وتحتاج إلى تجديد', 'ku' => 'مۆڵەتی هاوردە کە بەسەرچووە و پێویستی بە نوێکردنەوەیە'],
                'compliance_type' => 'license',
                'category' => 'business',
                'regulatory_body' => 'Ministry of Trade - Iraq',
                'reference_number' => 'IMP-2023-998877',
                'issue_date' => now()->subMonths(24),
                'expiry_date' => now()->subDays(30), // Already expired
                'priority' => 'critical',
                'risk_level' => 'very_high',
                'responsible_person_id' => $users->count() > 1 ? $users[1]->id : $users[0]->id,
                'cost' => 800000,
                'currency' => 'IQD',
                'requirements' => ['Import documentation', 'Customs clearance', 'Product specifications'],
                'reminder_days' => 30,
                'status' => 'expired',
                'is_active' => true,
            ],
        ];

        foreach ($complianceItems as $itemData) {
            $item = ComplianceItem::create($itemData);
            $item->updateComplianceScore();
        }

        // Create Inspections
        $items = ComplianceItem::all();

        foreach ($items->take(5) as $item) {
            // Past inspection
            $pastInspection = Inspection::create([
                'compliance_item_id' => $item->id,
                'inspection_type' => 'routine',
                'inspector_name' => 'Ahmed Al-Rashid',
                'inspector_organization' => $item->regulatory_body,
                'inspector_contact' => '+964 770 123 4567',
                'scheduled_date' => now()->subDays(rand(30, 90)),
                'actual_date' => now()->subDays(rand(25, 85)),
                'duration_hours' => rand(2, 8),
                'status' => 'completed',
                'result' => rand(1, 10) > 2 ? 'passed' : 'failed',
                'score' => rand(70, 95),
                'findings' => [
                    ['finding' => 'Documentation is complete and up to date', 'severity' => 'low'],
                    ['finding' => 'Minor procedural improvements needed', 'severity' => 'medium'],
                ],
                'recommendations' => [
                    ['recommendation' => 'Update emergency procedures', 'priority' => 'medium'],
                    ['recommendation' => 'Conduct staff training', 'priority' => 'high'],
                ],
                'follow_up_required' => rand(1, 10) > 7,
                'certificate_issued' => true,
                'certificate_number' => 'CERT-' . rand(100000, 999999),
                'certificate_expiry' => now()->addMonths(12),
                'next_inspection_date' => now()->addMonths(rand(6, 12)),
                'conducted_by_id' => $users->random()->id,
                'notes' => 'Inspection completed successfully with minor recommendations.',
            ]);

            // Future inspection
            if (rand(1, 10) > 5) {
                Inspection::create([
                    'compliance_item_id' => $item->id,
                    'inspection_type' => 'follow_up',
                    'inspector_name' => 'Sara Mohammed',
                    'inspector_organization' => $item->regulatory_body,
                    'inspector_contact' => '+964 750 234 5678',
                    'scheduled_date' => now()->addDays(rand(7, 60)),
                    'status' => 'scheduled',
                    'conducted_by_id' => $users->random()->id,
                    'notes' => 'Follow-up inspection scheduled.',
                ]);
            }
        }

        // Create Violations
        $expiredItem = ComplianceItem::where('status', 'expired')->first();
        if ($expiredItem) {
            ComplianceViolation::create([
                'compliance_item_id' => $expiredItem->id,
                'violation_type' => 'expiry',
                'title' => ['en' => 'License Expiry Violation', 'ar' => 'مخالفة انتهاء صلاحية الرخصة', 'ku' => 'پێشێلکاری بەسەرچوونی ماوەی مۆڵەت'],
                'description' => ['en' => 'Import license has expired and operations continue without valid license', 'ar' => 'انتهت صلاحية رخصة الاستيراد وتستمر العمليات بدون رخصة صالحة', 'ku' => 'مۆڵەتی هاوردە بەسەرچووە و کارەکان بەردەوامن بەبێ مۆڵەتی دروست'],
                'severity' => 'critical',
                'detected_date' => now()->subDays(5),
                'reported_by_id' => $users->random()->id,
                'assigned_to_id' => $users->random()->id,
                'status' => 'open',
                'follow_up_required' => true,
                'follow_up_date' => now()->addDays(7),
                'fine_amount' => 1000000,
                'fine_due_date' => now()->addDays(30),
                'escalation_level' => 'management',
            ]);
        }

        // Create some resolved violations
        foreach ($items->take(3) as $item) {
            if (rand(1, 10) > 6) {
                ComplianceViolation::create([
                    'compliance_item_id' => $item->id,
                    'violation_type' => 'documentation',
                    'title' => ['en' => 'Documentation Deficiency', 'ar' => 'نقص في الوثائق', 'ku' => 'کەمی بەڵگەنامە'],
                    'description' => ['en' => 'Missing required documentation for compliance verification', 'ar' => 'وثائق مطلوبة مفقودة للتحقق من الامتثال', 'ku' => 'بەڵگەنامە پێویستەکان بۆ پشتڕاستکردنەوەی گونجاندن نەماون'],
                    'severity' => 'medium',
                    'detected_date' => now()->subDays(rand(10, 30)),
                    'reported_by_id' => $users->random()->id,
                    'assigned_to_id' => $users->random()->id,
                    'status' => 'resolved',
                    'resolution_date' => now()->subDays(rand(1, 10)),
                    'resolution_description' => ['en' => 'All required documents have been submitted and verified', 'ar' => 'تم تقديم جميع الوثائق المطلوبة والتحقق منها', 'ku' => 'هەموو بەڵگەنامە پێویستەکان پێشکەش کراون و پشتڕاست کراونەتەوە'],
                    'corrective_actions' => [
                        ['action' => 'Document submission', 'status' => 'completed'],
                        ['action' => 'Process review', 'status' => 'completed'],
                    ],
                ]);
            }
        }

        $this->command->info('Compliance sample data seeded successfully!');
    }
}
