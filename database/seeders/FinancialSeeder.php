<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Financial\Models\Collection;
use App\Modules\Financial\Models\PaymentPlan;
use App\Modules\Customer\Models\Customer;
use App\Modules\Sales\Models\Sale;
use App\Models\User;

class FinancialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $customers = Customer::take(3)->get();
        $user = User::first();
        $sales = Sale::where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)->take(5)->get();

        if ($customers->count() === 0 || !$user) {
            $this->command->warn('No customers or users found. Please run customer and sales seeders first.');
            return;
        }

        // Create collections for outstanding sales
        foreach ($customers as $customer) {
            $customerSales = $sales->where('customer_id', $customer->id);

            if ($customerSales->count() > 0) {
                $totalOutstanding = $customerSales->sum(function ($sale) {
                    return $sale->total_amount - $sale->paid_amount;
                });

                if ($totalOutstanding > 0) {
                    $collection = Collection::create([
                        'collection_number' => 'TEMP-' . time() . '-' . $customer->id,
                        'customer_id' => $customer->id,
                        'collector_id' => $user->id,
                        'collection_date' => now(),
                        'due_date' => now()->addDays(rand(1, 30)),
                        'amount_due' => $totalOutstanding,
                        'amount_collected' => $totalOutstanding * (rand(0, 70) / 100), // 0-70% collected
                        'collection_method' => collect(['cash', 'bank_transfer', 'cheque'])->random(),
                        'status' => collect(['pending', 'in_progress', 'partial'])->random(),
                        'priority' => collect(['low', 'medium', 'high', 'urgent'])->random(),
                        'notes' => [
                            'en' => 'Collection for outstanding invoices',
                            'ar' => 'تحصيل للفواتير المستحقة',
                            'ku' => 'کۆکردنەوە بۆ پسوڵە باقییەکان'
                        ],
                        'contact_attempts' => rand(0, 5),
                        'last_contact_date' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                        'follow_up_date' => rand(0, 1) ? now()->addDays(rand(1, 7)) : null,
                    ]);

                    $collection->collection_number = $collection->generateCollectionNumber();
                    $collection->updateStatus();
                    $collection->save();

                    // Link to sales
                    foreach ($customerSales as $sale) {
                        $balanceAmount = $sale->total_amount - $sale->paid_amount;
                        if ($balanceAmount > 0) {
                            $collection->sales()->attach($sale->id, [
                                'amount_allocated' => $balanceAmount
                            ]);
                        }
                    }

                    // Add some activities
                    $activities = [
                        ['contact_attempt', 'Called customer - no answer'],
                        ['contact_attempt', 'Sent email reminder'],
                        ['contact_attempt', 'Customer promised payment by end of week'],
                        ['note_added', 'Customer experiencing temporary cash flow issues'],
                        ['follow_up_scheduled', 'Scheduled follow-up call for next week'],
                    ];

                    foreach (array_slice($activities, 0, rand(2, 4)) as $activity) {
                        $collection->addActivity($activity[0], $activity[1]);
                    }

                    // Add some payments for partial collections
                    if ($collection->amount_collected > 0) {
                        $paymentCount = rand(1, 3);
                        $remainingAmount = $collection->amount_collected;

                        for ($i = 0; $i < $paymentCount && $remainingAmount > 0; $i++) {
                            $paymentAmount = $i === $paymentCount - 1
                                ? $remainingAmount
                                : $remainingAmount * (rand(20, 80) / 100);

                            $collection->payments()->create([
                                'amount' => $paymentAmount,
                                'payment_method' => collect(['cash', 'bank_transfer', 'cheque'])->random(),
                                'payment_date' => now()->subDays(rand(1, 15)),
                                'reference' => 'REF-' . time() . '-' . rand(1000, 9999),
                                'user_id' => $user->id,
                                'status' => 'confirmed',
                            ]);

                            $remainingAmount -= $paymentAmount;
                        }
                    }
                }
            }
        }

        // Create some payment plans
        foreach ($customers->take(2) as $customer) {
            $paymentPlan = PaymentPlan::create([
                'plan_number' => 'TEMP-PP-' . time() . '-' . $customer->id,
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'total_amount' => rand(5000, 20000),
                'paid_amount' => 0,
                'installment_count' => rand(6, 12),
                'installment_amount' => 0, // Will be calculated
                'frequency' => collect(['weekly', 'monthly'])->random(),
                'start_date' => now()->addDays(rand(1, 7)),
                'end_date' => now()->addMonths(rand(6, 12)),
                'status' => 'active',
                'interest_rate' => rand(0, 5),
                'penalty_rate' => rand(1, 3),
                'notes' => 'Payment plan for customer ' . $customer->name,
            ]);

            $paymentPlan->plan_number = $paymentPlan->generatePlanNumber();
            $paymentPlan->installment_amount = $paymentPlan->total_amount / $paymentPlan->installment_count;
            $paymentPlan->save();

            // Generate installments
            $paymentPlan->generateInstallments();

            // Make some payments on the plan
            $paidInstallments = rand(0, 3);
            for ($i = 0; $i < $paidInstallments; $i++) {
                $paymentPlan->processPayment($paymentPlan->installment_amount, 'cash');
            }
        }

        $this->command->info('Financial collections data seeded successfully!');
    }
}
