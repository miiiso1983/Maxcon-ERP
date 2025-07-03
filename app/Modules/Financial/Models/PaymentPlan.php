<?php

namespace App\Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Modules\Customer\Models\Customer;
use App\Models\User;

class PaymentPlan extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'plan_number',
        'customer_id',
        'user_id',
        'total_amount',
        'paid_amount',
        'installment_count',
        'installment_amount',
        'frequency',
        'start_date',
        'end_date',
        'status',
        'interest_rate',
        'penalty_rate',
        'notes',
        'auto_debit',
        'bank_details',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'penalty_rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_debit' => 'boolean',
        'bank_details' => 'array',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DEFAULTED = 'defaulted';
    const STATUS_CANCELLED = 'cancelled';

    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_BIWEEKLY = 'biweekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_QUARTERLY = 'quarterly';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'plan_number', 'customer_id', 'total_amount', 
                'paid_amount', 'status'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function installments()
    {
        return $this->hasMany(PaymentPlanInstallment::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOverdue($query)
    {
        return $query->whereHas('installments', function ($q) {
            $q->where('due_date', '<', now())
              ->where('status', 'pending');
        });
    }

    // Accessors
    public function getBalanceAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return ($this->paid_amount / $this->total_amount) * 100;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_DEFAULTED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    public function getNextInstallmentAttribute()
    {
        return $this->installments()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();
    }

    public function getOverdueInstallmentsAttribute()
    {
        return $this->installments()
            ->where('due_date', '<', now())
            ->where('status', 'pending')
            ->get();
    }

    // Methods
    public function generatePlanNumber(): string
    {
        $prefix = 'PP';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id ?? 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    public function generateInstallments(): void
    {
        // Clear existing installments
        $this->installments()->delete();

        $installmentAmount = $this->installment_amount;
        $currentDate = $this->start_date;

        for ($i = 1; $i <= $this->installment_count; $i++) {
            // Adjust last installment for any rounding differences
            if ($i === $this->installment_count) {
                $totalPaid = ($this->installment_count - 1) * $installmentAmount;
                $installmentAmount = $this->total_amount - $totalPaid;
            }

            $this->installments()->create([
                'installment_number' => $i,
                'due_date' => $currentDate,
                'amount' => $installmentAmount,
                'status' => 'pending',
            ]);

            // Calculate next due date based on frequency
            $currentDate = $this->getNextDueDate($currentDate);
        }
    }

    private function getNextDueDate($currentDate)
    {
        return match($this->frequency) {
            self::FREQUENCY_WEEKLY => $currentDate->addWeek(),
            self::FREQUENCY_BIWEEKLY => $currentDate->addWeeks(2),
            self::FREQUENCY_MONTHLY => $currentDate->addMonth(),
            self::FREQUENCY_QUARTERLY => $currentDate->addMonths(3),
            default => $currentDate->addMonth(),
        };
    }

    public function processPayment(float $amount, string $method = 'cash'): void
    {
        $this->paid_amount += $amount;
        
        // Update status if fully paid
        if ($this->paid_amount >= $this->total_amount) {
            $this->status = self::STATUS_COMPLETED;
        }
        
        $this->save();

        // Apply payment to oldest pending installments
        $remainingAmount = $amount;
        $pendingInstallments = $this->installments()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->get();

        foreach ($pendingInstallments as $installment) {
            if ($remainingAmount <= 0) break;

            $paymentAmount = min($remainingAmount, $installment->balance_amount);
            $installment->addPayment($paymentAmount, $method);
            $remainingAmount -= $paymentAmount;
        }
    }

    public function calculatePenalty(): float
    {
        $penalty = 0;
        $overdueInstallments = $this->overdue_installments;

        foreach ($overdueInstallments as $installment) {
            $daysOverdue = $installment->due_date->diffInDays(now());
            $installmentPenalty = $installment->amount * ($this->penalty_rate / 100) * ($daysOverdue / 30);
            $penalty += $installmentPenalty;
        }

        return $penalty;
    }

    public function markAsDefaulted(string $reason): void
    {
        $this->status = self::STATUS_DEFAULTED;
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Defaulted: {$reason}";
        $this->save();

        // Create collection record for remaining balance
        Collection::create([
            'customer_id' => $this->customer_id,
            'amount_due' => $this->balance_amount,
            'due_date' => now(),
            'status' => Collection::STATUS_PENDING,
            'priority' => Collection::PRIORITY_HIGH,
            'payment_plan_id' => $this->id,
            'notes' => ['en' => "Collection for defaulted payment plan {$this->plan_number}"],
        ]);
    }

    public function canBeModified(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE]);
    }
}
