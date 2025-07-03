<?php

namespace App\Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentPlanInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'installment_number',
        'due_date',
        'amount',
        'paid_amount',
        'penalty_amount',
        'status',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_OVERDUE = 'overdue';

    // Relationships
    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function payments()
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    // Accessors
    public function getBalanceAmountAttribute(): float
    {
        return $this->amount - $this->paid_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date->isPast() && $this->status !== self::STATUS_PAID;
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return $this->due_date->diffInDays(now());
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_PAID => 'success',
            self::STATUS_PARTIAL => 'info',
            self::STATUS_OVERDUE => 'danger',
            default => 'secondary',
        };
    }

    // Methods
    public function addPayment(float $amount, string $method = 'cash'): InstallmentPayment
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
        ]);

        $this->paid_amount += $amount;
        $this->updateStatus();
        $this->save();

        return $payment;
    }

    public function updateStatus(): void
    {
        if ($this->paid_amount <= 0) {
            $this->status = $this->is_overdue ? self::STATUS_OVERDUE : self::STATUS_PENDING;
        } elseif ($this->paid_amount >= $this->amount) {
            $this->status = self::STATUS_PAID;
            $this->paid_date = now();
        } else {
            $this->status = self::STATUS_PARTIAL;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($installment) {
            $installment->updateStatus();
        });
    }
}
