<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\LeaveRequest;
use Carbon\Carbon;

class HRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Departments
        $departments = [
            [
                'name' => ['en' => 'Human Resources', 'ar' => 'الموارد البشرية', 'ku' => 'سەرچاوە مرۆییەکان'],
                'description' => ['en' => 'HR Department', 'ar' => 'قسم الموارد البشرية', 'ku' => 'بەشی سەرچاوە مرۆییەکان'],
                'code' => 'HR',
                'budget' => 50000,
                'location' => 'Baghdad Office',
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Sales', 'ar' => 'المبيعات', 'ku' => 'فرۆشتن'],
                'description' => ['en' => 'Sales Department', 'ar' => 'قسم المبيعات', 'ku' => 'بەشی فرۆشتن'],
                'code' => 'SAL',
                'budget' => 100000,
                'location' => 'Baghdad Office',
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Finance', 'ar' => 'المالية', 'ku' => 'دارایی'],
                'description' => ['en' => 'Finance Department', 'ar' => 'قسم المالية', 'ku' => 'بەشی دارایی'],
                'code' => 'FIN',
                'budget' => 75000,
                'location' => 'Baghdad Office',
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'IT', 'ar' => 'تقنية المعلومات', 'ku' => 'تەکنەلۆژیای زانیاری'],
                'description' => ['en' => 'Information Technology', 'ar' => 'تقنية المعلومات', 'ku' => 'تەکنەلۆژیای زانیاری'],
                'code' => 'IT',
                'budget' => 80000,
                'location' => 'Baghdad Office',
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Operations', 'ar' => 'العمليات', 'ku' => 'کارەکان'],
                'description' => ['en' => 'Operations Department', 'ar' => 'قسم العمليات', 'ku' => 'بەشی کارەکان'],
                'code' => 'OPS',
                'budget' => 120000,
                'location' => 'Baghdad Office',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $deptData) {
            Department::create($deptData);
        }

        // Create Sample Employees
        $employees = [
            [
                'employee_code' => 'HR2501',
                'first_name' => 'Ahmed',
                'last_name' => 'Al-Rashid',
                'arabic_name' => 'أحمد الراشد',
                'kurdish_name' => 'ئەحمەد ڕاشید',
                'email' => 'ahmed.rashid@maxcon.iq',
                'phone' => '+964 770 123 4567',
                'national_id' => '19850101001',
                'date_of_birth' => '1985-01-01',
                'gender' => 'male',
                'marital_status' => 'married',
                'nationality' => 'Iraqi',
                'address' => 'Baghdad, Karrada District',
                'emergency_contact_name' => 'Fatima Al-Rashid',
                'emergency_contact_phone' => '+964 770 987 6543',
                'hire_date' => '2020-01-15',
                'employment_status' => 'active',
                'job_title' => ['en' => 'HR Manager', 'ar' => 'مدير الموارد البشرية', 'ku' => 'بەڕێوەبەری سەرچاوە مرۆییەکان'],
                'department_id' => 1,
                'salary_amount' => 1500000,
                'salary_currency' => 'IQD',
                'salary_type' => 'monthly',
                'contract_type' => 'permanent',
                'bank_account_number' => '1234567890',
                'bank_name' => 'Rafidain Bank',
                'is_active' => true,
            ],
            [
                'employee_code' => 'SAL2502',
                'first_name' => 'Sara',
                'last_name' => 'Mohammed',
                'arabic_name' => 'سارة محمد',
                'kurdish_name' => 'سارا محەمەد',
                'email' => 'sara.mohammed@maxcon.iq',
                'phone' => '+964 750 234 5678',
                'national_id' => '19900215002',
                'date_of_birth' => '1990-02-15',
                'gender' => 'female',
                'marital_status' => 'single',
                'nationality' => 'Iraqi',
                'address' => 'Baghdad, Mansour District',
                'emergency_contact_name' => 'Ali Mohammed',
                'emergency_contact_phone' => '+964 750 876 5432',
                'hire_date' => '2021-03-01',
                'employment_status' => 'active',
                'job_title' => ['en' => 'Sales Representative', 'ar' => 'مندوب مبيعات', 'ku' => 'نوێنەری فرۆشتن'],
                'department_id' => 2,
                'salary_amount' => 1200000,
                'salary_currency' => 'IQD',
                'salary_type' => 'monthly',
                'contract_type' => 'permanent',
                'bank_account_number' => '2345678901',
                'bank_name' => 'Rasheed Bank',
                'is_active' => true,
            ],
            [
                'employee_code' => 'FIN2503',
                'first_name' => 'Omar',
                'last_name' => 'Hassan',
                'arabic_name' => 'عمر حسن',
                'kurdish_name' => 'عومەر حەسەن',
                'email' => 'omar.hassan@maxcon.iq',
                'phone' => '+964 780 345 6789',
                'national_id' => '19880310003',
                'date_of_birth' => '1988-03-10',
                'gender' => 'male',
                'marital_status' => 'married',
                'nationality' => 'Iraqi',
                'address' => 'Baghdad, Jadriya District',
                'emergency_contact_name' => 'Zeinab Hassan',
                'emergency_contact_phone' => '+964 780 765 4321',
                'hire_date' => '2019-06-01',
                'employment_status' => 'active',
                'job_title' => ['en' => 'Financial Analyst', 'ar' => 'محلل مالي', 'ku' => 'شیکارەوەی دارایی'],
                'department_id' => 3,
                'salary_amount' => 1400000,
                'salary_currency' => 'IQD',
                'salary_type' => 'monthly',
                'contract_type' => 'permanent',
                'bank_account_number' => '3456789012',
                'bank_name' => 'Commercial Bank of Iraq',
                'is_active' => true,
            ],
            [
                'employee_code' => 'IT2504',
                'first_name' => 'Layla',
                'last_name' => 'Ahmad',
                'arabic_name' => 'ليلى أحمد',
                'kurdish_name' => 'لەیلا ئەحمەد',
                'email' => 'layla.ahmad@maxcon.iq',
                'phone' => '+964 790 456 7890',
                'national_id' => '19920420004',
                'date_of_birth' => '1992-04-20',
                'gender' => 'female',
                'marital_status' => 'single',
                'nationality' => 'Iraqi',
                'address' => 'Baghdad, Adhamiya District',
                'emergency_contact_name' => 'Khalil Ahmad',
                'emergency_contact_phone' => '+964 790 654 3210',
                'hire_date' => '2022-01-10',
                'employment_status' => 'active',
                'job_title' => ['en' => 'Software Developer', 'ar' => 'مطور برمجيات', 'ku' => 'گەشەپێدەری نەرمەکاڵا'],
                'department_id' => 4,
                'salary_amount' => 1600000,
                'salary_currency' => 'IQD',
                'salary_type' => 'monthly',
                'contract_type' => 'permanent',
                'bank_account_number' => '4567890123',
                'bank_name' => 'Iraqi Islamic Bank',
                'is_active' => true,
            ],
            [
                'employee_code' => 'OPS2505',
                'first_name' => 'Mustafa',
                'last_name' => 'Ali',
                'arabic_name' => 'مصطفى علي',
                'kurdish_name' => 'مستەفا عەلی',
                'email' => 'mustafa.ali@maxcon.iq',
                'phone' => '+964 760 567 8901',
                'national_id' => '19870525005',
                'date_of_birth' => '1987-05-25',
                'gender' => 'male',
                'marital_status' => 'married',
                'nationality' => 'Iraqi',
                'address' => 'Baghdad, Sadr City',
                'emergency_contact_name' => 'Amina Ali',
                'emergency_contact_phone' => '+964 760 543 2109',
                'hire_date' => '2020-09-01',
                'employment_status' => 'active',
                'job_title' => ['en' => 'Operations Supervisor', 'ar' => 'مشرف العمليات', 'ku' => 'سەرپەرشتیاری کارەکان'],
                'department_id' => 5,
                'salary_amount' => 1300000,
                'salary_currency' => 'IQD',
                'salary_type' => 'monthly',
                'contract_type' => 'permanent',
                'bank_account_number' => '5678901234',
                'bank_name' => 'Kurdistan International Bank',
                'is_active' => true,
            ],
        ];

        foreach ($employees as $empData) {
            Employee::create($empData);
        }

        // Create Sample Attendance Records for the last 30 days
        $employees = Employee::all();
        $startDate = now()->subDays(30);

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);

            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($employees as $employee) {
                // 90% attendance rate
                if (rand(1, 100) <= 90) {
                    $checkIn = $date->copy()->setTime(8, rand(0, 30), 0); // 8:00-8:30 AM
                    $checkOut = $date->copy()->setTime(17, rand(0, 30), 0); // 5:00-5:30 PM

                    $isLate = $checkIn->hour > 8 || ($checkIn->hour == 8 && $checkIn->minute > 15);
                    $hoursWorked = $checkIn->diffInHours($checkOut);

                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $date->format('Y-m-d'),
                        'check_in_time' => $checkIn,
                        'check_out_time' => $checkOut,
                        'hours_worked' => $hoursWorked,
                        'overtime_hours' => $hoursWorked > 8 ? $hoursWorked - 8 : 0,
                        'status' => $isLate ? Attendance::STATUS_LATE : Attendance::STATUS_PRESENT,
                        'is_late' => $isLate,
                        'late_minutes' => $isLate ? $checkIn->minute : 0,
                    ]);
                } else {
                    // Absent
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $date->format('Y-m-d'),
                        'status' => Attendance::STATUS_ABSENT,
                        'hours_worked' => 0,
                    ]);
                }
            }
        }

        // Create Sample Leave Requests
        $leaveRequests = [
            [
                'employee_id' => 1,
                'leave_type' => LeaveRequest::TYPE_ANNUAL,
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(14),
                'days_requested' => 5,
                'reason' => 'Family vacation to Erbil',
                'status' => LeaveRequest::STATUS_PENDING,
                'emergency_contact' => 'Fatima Al-Rashid',
                'emergency_phone' => '+964 770 987 6543',
            ],
            [
                'employee_id' => 2,
                'leave_type' => LeaveRequest::TYPE_SICK,
                'start_date' => now()->subDays(2),
                'end_date' => now()->subDays(1),
                'days_requested' => 2,
                'reason' => 'Flu symptoms',
                'status' => LeaveRequest::STATUS_APPROVED,
                'approved_by' => 1,
                'approved_at' => now()->subDays(3),
            ],
            [
                'employee_id' => 3,
                'leave_type' => LeaveRequest::TYPE_EMERGENCY,
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(5),
                'days_requested' => 1,
                'reason' => 'Family emergency',
                'status' => LeaveRequest::STATUS_PENDING,
                'emergency_contact' => 'Zeinab Hassan',
                'emergency_phone' => '+964 780 765 4321',
            ],
        ];

        foreach ($leaveRequests as $leaveData) {
            LeaveRequest::create($leaveData);
        }

        $this->command->info('HR sample data seeded successfully!');
    }
}
