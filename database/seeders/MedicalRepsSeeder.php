<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\MedicalReps\Models\Territory;
use App\Modules\MedicalReps\Models\MedicalRep;
use App\Modules\MedicalReps\Models\CustomerVisit;
use App\Modules\HR\Models\Employee;
use App\Modules\Customer\Models\Customer;
use Carbon\Carbon;

class MedicalRepsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Territories
        $territories = [
            [
                'name' => ['en' => 'Baghdad Central', 'ar' => 'بغداد المركز', 'ku' => 'بەغدای ناوەند'],
                'description' => ['en' => 'Central Baghdad territory covering Karrada, Mansour, and Jadriya', 'ar' => 'منطقة بغداد المركزية تشمل الكرادة والمنصور والجادرية', 'ku' => 'ناوچەی بەغدای ناوەند کە کەرادە، مەنسوور و جادریا دەگرێتەوە'],
                'code' => 'BGD-C',
                'region' => 'baghdad',
                'province' => 'Baghdad',
                'cities' => ['Baghdad'],
                'population' => 2500000,
                'market_potential' => 5000000,
                'competition_level' => 'high',
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Baghdad North', 'ar' => 'بغداد الشمال', 'ku' => 'بەغدای باکوور'],
                'description' => ['en' => 'Northern Baghdad including Adhamiya and Sadr City', 'ar' => 'شمال بغداد بما في ذلك الأعظمية ومدينة الصدر', 'ku' => 'باکووری بەغداد کە ئەعزەمیە و شاری سەدر دەگرێتەوە'],
                'code' => 'BGD-N',
                'region' => 'baghdad',
                'province' => 'Baghdad',
                'cities' => ['Baghdad'],
                'population' => 1800000,
                'market_potential' => 3500000,
                'competition_level' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Erbil Territory', 'ar' => 'منطقة أربيل', 'ku' => 'ناوچەی هەولێر'],
                'description' => ['en' => 'Kurdistan Region - Erbil and surrounding areas', 'ar' => 'إقليم كردستان - أربيل والمناطق المحيطة', 'ku' => 'هەرێمی کوردستان - هەولێر و دەوروبەری'],
                'code' => 'ERB',
                'region' => 'erbil',
                'province' => 'Erbil',
                'cities' => ['Erbil', 'Shaqlawa', 'Koya'],
                'population' => 1500000,
                'market_potential' => 4000000,
                'competition_level' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Basra Territory', 'ar' => 'منطقة البصرة', 'ku' => 'ناوچەی بەسرە'],
                'description' => ['en' => 'Southern Iraq - Basra and oil-rich regions', 'ar' => 'جنوب العراق - البصرة والمناطق الغنية بالنفط', 'ku' => 'باشووری عێراق - بەسرە و ناوچە دەوڵەمەندەکانی نەوت'],
                'code' => 'BSR',
                'region' => 'basra',
                'province' => 'Basra',
                'cities' => ['Basra', 'Zubair', 'Safwan'],
                'population' => 2200000,
                'market_potential' => 6000000,
                'competition_level' => 'high',
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Mosul Territory', 'ar' => 'منطقة الموصل', 'ku' => 'ناوچەی موسڵ'],
                'description' => ['en' => 'Northern Iraq - Mosul and Nineveh province', 'ar' => 'شمال العراق - الموصل ومحافظة نينوى', 'ku' => 'باکووری عێراق - موسڵ و پارێزگای نەینەوا'],
                'code' => 'MSL',
                'region' => 'mosul',
                'province' => 'Nineveh',
                'cities' => ['Mosul', 'Tal Afar', 'Sinjar'],
                'population' => 1200000,
                'market_potential' => 2500000,
                'competition_level' => 'low',
                'is_active' => true,
            ],
        ];

        foreach ($territories as $territoryData) {
            Territory::create($territoryData);
        }

        // Get existing employees to assign as medical reps
        $employees = Employee::active()->take(8)->get();

        if ($employees->count() < 3) {
            $this->command->warn('Not enough employees found. Please run HR seeder first.');
            return;
        }

        // Create Medical Representatives
        $medicalReps = [
            [
                'employee_id' => $employees[0]->id,
                'rep_code' => 'REPBGD2501',
                'specialization' => ['en' => 'Cardiology & Internal Medicine', 'ar' => 'أمراض القلب والطب الباطني', 'ku' => 'نەخۆشی دڵ و پزیشکی ناوخۆیی'],
                'license_number' => 'MED-REP-001-2025',
                'license_expiry' => now()->addYears(2),
                'territory_id' => 1,
                'commission_rate' => 0.08,
                'base_salary' => 2000000,
                'target_monthly' => 15000000,
                'target_quarterly' => 45000000,
                'target_annual' => 180000000,
                'phone_allowance' => 100000,
                'fuel_allowance' => 300000,
                'medical_allowance' => 150000,
                'education_level' => 'Bachelor in Pharmacy',
                'certifications' => ['Medical Sales Certification', 'Pharmaceutical Knowledge Certificate'],
                'languages_spoken' => ['Arabic', 'English', 'Kurdish'],
                'start_date' => now()->subMonths(6),
                'status' => 'active',
                'performance_rating' => 'excellent',
                'is_active' => true,
            ],
            [
                'employee_id' => $employees[1]->id,
                'rep_code' => 'REPBGD2502',
                'specialization' => ['en' => 'Orthopedics & Surgery', 'ar' => 'العظام والجراحة', 'ku' => 'ئێسک و نەشتەرگەری'],
                'license_number' => 'MED-REP-002-2025',
                'license_expiry' => now()->addYears(2),
                'territory_id' => 2,
                'commission_rate' => 0.07,
                'base_salary' => 1800000,
                'target_monthly' => 12000000,
                'target_quarterly' => 36000000,
                'target_annual' => 144000000,
                'phone_allowance' => 100000,
                'fuel_allowance' => 250000,
                'medical_allowance' => 150000,
                'education_level' => 'Bachelor in Medicine',
                'certifications' => ['Medical Sales Certification'],
                'languages_spoken' => ['Arabic', 'English'],
                'start_date' => now()->subMonths(4),
                'status' => 'active',
                'performance_rating' => 'good',
                'is_active' => true,
            ],
            [
                'employee_id' => $employees[2]->id,
                'rep_code' => 'REPERB2503',
                'specialization' => ['en' => 'Pediatrics & Family Medicine', 'ar' => 'طب الأطفال وطب الأسرة', 'ku' => 'پزیشکی منداڵان و پزیشکی خێزان'],
                'license_number' => 'MED-REP-003-2025',
                'license_expiry' => now()->addYears(2),
                'territory_id' => 3,
                'commission_rate' => 0.06,
                'base_salary' => 1600000,
                'target_monthly' => 10000000,
                'target_quarterly' => 30000000,
                'target_annual' => 120000000,
                'phone_allowance' => 80000,
                'fuel_allowance' => 200000,
                'medical_allowance' => 120000,
                'education_level' => 'Bachelor in Pharmacy',
                'certifications' => ['Medical Sales Certification', 'Pediatric Medicine Certificate'],
                'languages_spoken' => ['Kurdish', 'Arabic', 'English'],
                'start_date' => now()->subMonths(8),
                'status' => 'active',
                'performance_rating' => 'good',
                'is_active' => true,
            ],
        ];

        // Add more reps if we have more employees
        if ($employees->count() >= 5) {
            $medicalReps[] = [
                'employee_id' => $employees[3]->id,
                'rep_code' => 'REPBSR2504',
                'specialization' => ['en' => 'Oncology & Hematology', 'ar' => 'الأورام وأمراض الدم', 'ku' => 'شێرپەنجە و نەخۆشی خوێن'],
                'license_number' => 'MED-REP-004-2025',
                'license_expiry' => now()->addYears(2),
                'territory_id' => 4,
                'commission_rate' => 0.09,
                'base_salary' => 2200000,
                'target_monthly' => 18000000,
                'target_quarterly' => 54000000,
                'target_annual' => 216000000,
                'phone_allowance' => 120000,
                'fuel_allowance' => 350000,
                'medical_allowance' => 180000,
                'education_level' => 'Master in Pharmaceutical Sciences',
                'certifications' => ['Medical Sales Certification', 'Oncology Specialist Certificate'],
                'languages_spoken' => ['Arabic', 'English'],
                'start_date' => now()->subMonths(12),
                'status' => 'active',
                'performance_rating' => 'excellent',
                'is_active' => true,
            ];

            $medicalReps[] = [
                'employee_id' => $employees[4]->id,
                'rep_code' => 'REPMSL2505',
                'specialization' => ['en' => 'Neurology & Psychiatry', 'ar' => 'الأعصاب والطب النفسي', 'ku' => 'دەمارناسی و پزیشکی دەروونی'],
                'license_number' => 'MED-REP-005-2025',
                'license_expiry' => now()->addYears(2),
                'territory_id' => 5,
                'commission_rate' => 0.07,
                'base_salary' => 1700000,
                'target_monthly' => 8000000,
                'target_quarterly' => 24000000,
                'target_annual' => 96000000,
                'phone_allowance' => 90000,
                'fuel_allowance' => 200000,
                'medical_allowance' => 130000,
                'education_level' => 'Bachelor in Pharmacy',
                'certifications' => ['Medical Sales Certification'],
                'languages_spoken' => ['Arabic', 'Kurdish'],
                'start_date' => now()->subMonths(3),
                'status' => 'active',
                'performance_rating' => 'average',
                'is_active' => true,
            ];
        }

        foreach ($medicalReps as $repData) {
            MedicalRep::create($repData);
        }

        // Create sample customer visits
        $reps = MedicalRep::all();
        $customers = Customer::take(20)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Skipping visit creation.');
            return;
        }

        foreach ($reps as $rep) {
            // Create visits for the last 30 days
            for ($i = 30; $i >= 0; $i--) {
                $visitDate = now()->subDays($i);

                // Skip weekends
                if ($visitDate->isWeekend()) {
                    continue;
                }

                // 70% chance of having visits on any given day
                if (rand(1, 100) <= 70) {
                    $numVisits = rand(1, 4); // 1-4 visits per day

                    for ($v = 0; $v < $numVisits; $v++) {
                        $customer = $customers->random();
                        $visitTime = $visitDate->copy()->setTime(rand(8, 17), rand(0, 59), 0);

                        $visitTypes = array_keys(CustomerVisit::getVisitTypes());
                        $purposes = array_keys(CustomerVisit::getPurposes());

                        $visit = CustomerVisit::create([
                            'medical_rep_id' => $rep->id,
                            'customer_id' => $customer->id,
                            'visit_date' => $visitDate->format('Y-m-d'),
                            'visit_time' => $visitTime,
                            'visit_type' => $visitTypes[array_rand($visitTypes)],
                            'purpose' => $purposes[array_rand($purposes)],
                            'status' => $i > 0 ? CustomerVisit::STATUS_COMPLETED : (rand(1, 100) <= 80 ? CustomerVisit::STATUS_COMPLETED : CustomerVisit::STATUS_PLANNED),
                            'is_planned' => true,
                            'planned_by' => $rep->id,
                        ]);

                        // If visit is completed, add check-in/out times
                        if ($visit->status === CustomerVisit::STATUS_COMPLETED) {
                            $checkInTime = $visitTime->copy()->addMinutes(rand(-10, 10));
                            $checkOutTime = $checkInTime->copy()->addMinutes(rand(30, 120));

                            $visit->update([
                                'check_in_time' => $checkInTime,
                                'check_out_time' => $checkOutTime,
                                'duration_minutes' => $checkInTime->diffInMinutes($checkOutTime),
                                'notes' => 'Sample visit completed successfully',
                                'outcomes' => [
                                    ['outcome' => 'Product presentation completed', 'details' => [], 'recorded_at' => $checkOutTime],
                                    ['outcome' => 'Customer feedback collected', 'details' => [], 'recorded_at' => $checkOutTime],
                                ],
                            ]);
                        }
                    }
                }
            }

            // Create some future visits
            for ($i = 1; $i <= 7; $i++) {
                $futureDate = now()->addDays($i);

                if (!$futureDate->isWeekend() && rand(1, 100) <= 60) {
                    $customer = $customers->random();
                    $visitTime = $futureDate->copy()->setTime(rand(9, 16), rand(0, 59), 0);

                    $visitTypes = array_keys(CustomerVisit::getVisitTypes());
                    $purposes = array_keys(CustomerVisit::getPurposes());

                    CustomerVisit::create([
                        'medical_rep_id' => $rep->id,
                        'customer_id' => $customer->id,
                        'visit_date' => $futureDate->format('Y-m-d'),
                        'visit_time' => $visitTime,
                        'visit_type' => $visitTypes[array_rand($visitTypes)],
                        'purpose' => $purposes[array_rand($purposes)],
                        'status' => CustomerVisit::STATUS_PLANNED,
                        'is_planned' => true,
                        'planned_by' => $rep->id,
                        'notes' => 'Scheduled follow-up visit',
                    ]);
                }
            }
        }

        $this->command->info('Medical Representatives sample data seeded successfully!');
    }
}
