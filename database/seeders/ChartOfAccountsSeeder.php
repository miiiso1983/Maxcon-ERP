<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Financial\Models\Account;
use App\Modules\Financial\Models\FinancialPeriod;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main account categories
        $accounts = [
            // ASSETS (1000-1999)
            [
                'account_code' => '1000',
                'account_name' => ['en' => 'Assets', 'ar' => 'الأصول', 'ku' => 'سامان'],
                'account_type' => Account::TYPE_ASSET,
                'is_system' => true,
                'children' => [
                    [
                        'account_code' => '1100',
                        'account_name' => ['en' => 'Current Assets', 'ar' => 'الأصول المتداولة', 'ku' => 'سامانی ئێستا'],
                        'account_type' => Account::TYPE_ASSET,
                        'children' => [
                            [
                                'account_code' => '1110',
                                'account_name' => ['en' => 'Cash', 'ar' => 'النقد', 'ku' => 'پارە'],
                                'account_type' => Account::TYPE_ASSET,
                                'opening_balance' => 100000,
                            ],
                            [
                                'account_code' => '1120',
                                'account_name' => ['en' => 'Bank Account', 'ar' => 'حساب البنك', 'ku' => 'حسابی بانک'],
                                'account_type' => Account::TYPE_ASSET,
                                'opening_balance' => 500000,
                            ],
                            [
                                'account_code' => '1200',
                                'account_name' => ['en' => 'Accounts Receivable', 'ar' => 'الذمم المدينة', 'ku' => 'قەرزی وەرگرتن'],
                                'account_type' => Account::TYPE_ASSET,
                            ],
                            [
                                'account_code' => '1300',
                                'account_name' => ['en' => 'Inventory', 'ar' => 'المخزون', 'ku' => 'کۆگا'],
                                'account_type' => Account::TYPE_ASSET,
                                'opening_balance' => 2000000,
                            ],
                        ]
                    ],
                    [
                        'account_code' => '1500',
                        'account_name' => ['en' => 'Fixed Assets', 'ar' => 'الأصول الثابتة', 'ku' => 'سامانی جێگیر'],
                        'account_type' => Account::TYPE_ASSET,
                        'children' => [
                            [
                                'account_code' => '1510',
                                'account_name' => ['en' => 'Equipment', 'ar' => 'المعدات', 'ku' => 'ئامێر'],
                                'account_type' => Account::TYPE_ASSET,
                                'opening_balance' => 1000000,
                            ],
                            [
                                'account_code' => '1520',
                                'account_name' => ['en' => 'Furniture & Fixtures', 'ar' => 'الأثاث والتجهيزات', 'ku' => 'کەلوپەل و ئامێر'],
                                'account_type' => Account::TYPE_ASSET,
                                'opening_balance' => 300000,
                            ],
                        ]
                    ],
                ]
            ],

            // LIABILITIES (2000-2999)
            [
                'account_code' => '2000',
                'account_name' => ['en' => 'Liabilities', 'ar' => 'الخصوم', 'ku' => 'قەرز'],
                'account_type' => Account::TYPE_LIABILITY,
                'is_system' => true,
                'children' => [
                    [
                        'account_code' => '2100',
                        'account_name' => ['en' => 'Current Liabilities', 'ar' => 'الخصوم المتداولة', 'ku' => 'قەرزی ئێستا'],
                        'account_type' => Account::TYPE_LIABILITY,
                        'children' => [
                            [
                                'account_code' => '2110',
                                'account_name' => ['en' => 'Accounts Payable', 'ar' => 'الذمم الدائنة', 'ku' => 'قەرزی دان'],
                                'account_type' => Account::TYPE_LIABILITY,
                            ],
                            [
                                'account_code' => '2200',
                                'account_name' => ['en' => 'Accrued Expenses', 'ar' => 'المصروفات المستحقة', 'ku' => 'خەرجی کۆکراوە'],
                                'account_type' => Account::TYPE_LIABILITY,
                            ],
                            [
                                'account_code' => '2300',
                                'account_name' => ['en' => 'Sales Tax Payable', 'ar' => 'ضريبة المبيعات المستحقة', 'ku' => 'باجی فرۆشتن'],
                                'account_type' => Account::TYPE_LIABILITY,
                                'tax_account' => true,
                            ],
                        ]
                    ],
                ]
            ],

            // EQUITY (3000-3999)
            [
                'account_code' => '3000',
                'account_name' => ['en' => 'Equity', 'ar' => 'حقوق الملكية', 'ku' => 'مافی خاوەن'],
                'account_type' => Account::TYPE_EQUITY,
                'is_system' => true,
                'children' => [
                    [
                        'account_code' => '3100',
                        'account_name' => ['en' => 'Owner\'s Capital', 'ar' => 'رأس مال المالك', 'ku' => 'سەرمایەی خاوەن'],
                        'account_type' => Account::TYPE_EQUITY,
                        'opening_balance' => 3000000,
                    ],
                    [
                        'account_code' => '3200',
                        'account_name' => ['en' => 'Retained Earnings', 'ar' => 'الأرباح المحتجزة', 'ku' => 'قازانجی پاشەکەوتکراو'],
                        'account_type' => Account::TYPE_EQUITY,
                    ],
                    [
                        'account_code' => '3900',
                        'account_name' => ['en' => 'Income Summary', 'ar' => 'ملخص الدخل', 'ku' => 'کورتەی داهات'],
                        'account_type' => Account::TYPE_EQUITY,
                        'is_system' => true,
                    ],
                ]
            ],

            // REVENUE (4000-4999)
            [
                'account_code' => '4000',
                'account_name' => ['en' => 'Revenue', 'ar' => 'الإيرادات', 'ku' => 'داهات'],
                'account_type' => Account::TYPE_REVENUE,
                'is_system' => true,
                'children' => [
                    [
                        'account_code' => '4100',
                        'account_name' => ['en' => 'Sales Revenue', 'ar' => 'إيرادات المبيعات', 'ku' => 'داهاتی فرۆشتن'],
                        'account_type' => Account::TYPE_REVENUE,
                    ],
                    [
                        'account_code' => '4200',
                        'account_name' => ['en' => 'Service Revenue', 'ar' => 'إيرادات الخدمات', 'ku' => 'داهاتی خزمەتگوزاری'],
                        'account_type' => Account::TYPE_REVENUE,
                    ],
                    [
                        'account_code' => '4900',
                        'account_name' => ['en' => 'Other Revenue', 'ar' => 'إيرادات أخرى', 'ku' => 'داهاتی تر'],
                        'account_type' => Account::TYPE_REVENUE,
                    ],
                ]
            ],

            // EXPENSES (5000-5999)
            [
                'account_code' => '5000',
                'account_name' => ['en' => 'Expenses', 'ar' => 'المصروفات', 'ku' => 'خەرج'],
                'account_type' => Account::TYPE_EXPENSE,
                'is_system' => true,
                'children' => [
                    [
                        'account_code' => '5100',
                        'account_name' => ['en' => 'Cost of Goods Sold', 'ar' => 'تكلفة البضاعة المباعة', 'ku' => 'تێچووی کاڵای فرۆشراو'],
                        'account_type' => Account::TYPE_EXPENSE,
                    ],
                    [
                        'account_code' => '5200',
                        'account_name' => ['en' => 'Operating Expenses', 'ar' => 'المصروفات التشغيلية', 'ku' => 'خەرجی کارکردن'],
                        'account_type' => Account::TYPE_EXPENSE,
                        'children' => [
                            [
                                'account_code' => '5210',
                                'account_name' => ['en' => 'Salaries & Wages', 'ar' => 'الرواتب والأجور', 'ku' => 'مووچە و کرێ'],
                                'account_type' => Account::TYPE_EXPENSE,
                            ],
                            [
                                'account_code' => '5220',
                                'account_name' => ['en' => 'Rent Expense', 'ar' => 'مصروف الإيجار', 'ku' => 'خەرجی کرێ'],
                                'account_type' => Account::TYPE_EXPENSE,
                            ],
                            [
                                'account_code' => '5230',
                                'account_name' => ['en' => 'Utilities Expense', 'ar' => 'مصروف المرافق', 'ku' => 'خەرجی خزمەتگوزاری'],
                                'account_type' => Account::TYPE_EXPENSE,
                            ],
                        ]
                    ],
                ]
            ],
        ];

        $this->createAccountsRecursively($accounts);

        // Create current financial period
        FinancialPeriod::create([
            'name' => now()->format('F Y'),
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'fiscal_year' => now()->year,
            'period_type' => FinancialPeriod::TYPE_MONTHLY,
        ]);

        $this->command->info('Chart of accounts seeded successfully!');
    }

    private function createAccountsRecursively(array $accounts, $parentId = null): void
    {
        foreach ($accounts as $accountData) {
            $children = $accountData['children'] ?? [];
            unset($accountData['children']);

            $account = Account::create(array_merge($accountData, [
                'parent_account_id' => $parentId,
                'current_balance' => $accountData['opening_balance'] ?? 0,
            ]));

            if (!empty($children)) {
                $this->createAccountsRecursively($children, $account->id);
            }
        }
    }
}
