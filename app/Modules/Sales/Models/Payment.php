<?php

namespace App\Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'sale_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference',
        'notes',
        'user_id',
        'status',
        'transaction_id',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'gateway_response' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const METHOD_CASH = 'cash';
    const METHOD_CARD = 'card';
    const METHOD_TRANSFER = 'transfer';
    const METHOD_CREDIT = 'credit';
    const METHOD_CHEQUE = 'cheque';
    const METHOD_DIGITAL_WALLET = 'digital_wallet';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sale_id', 'amount', 'payment_method', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            self::STATUS_REFUNDED => 'info',
            default => 'secondary',
        };
    }

    public function getMethodIconAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_CASH => 'fas fa-money-bill',
            self::METHOD_CARD => 'fas fa-credit-card',
            self::METHOD_TRANSFER => 'fas fa-exchange-alt',
            self::METHOD_CREDIT => 'fas fa-handshake',
            self::METHOD_CHEQUE => 'fas fa-file-invoice',
            self::METHOD_DIGITAL_WALLET => 'fas fa-wallet',
            default => 'fas fa-dollar-sign',
        };
    }

    // Methods
    public function generateReference(): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id ?? 1, 6, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    public function markAsCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();

        // Update sale payment status
        $this->sale->updatePaymentStatus();
        $this->sale->save();
    }

    public function markAsFailed(?string $reason = null): void
    {
        $this->status = self::STATUS_FAILED;
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Failed: {$reason}";
        }
        $this->save();
    }

    public function refund(?float $amount = null): void
    {
        $refundAmount = $amount ?? $this->amount;
        
        $this->status = self::STATUS_REFUNDED;
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Refunded: {$refundAmount}";
        $this->save();

        // Update sale paid amount
        $this->sale->paid_amount -= $refundAmount;
        $this->sale->updatePaymentStatus();
        $this->sale->save();
    }
}
