<?php

namespace App\Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class CollectionPayment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'collection_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference',
        'notes',
        'user_id',
        'status',
        'bank_details',
        'receipt_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'bank_details' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['collection_id', 'amount', 'payment_method', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_REFUNDED => 'info',
            default => 'secondary',
        };
    }

    // Methods
    public function generateReceiptNumber(): string
    {
        $prefix = 'REC';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id ?? 1, 6, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    public function confirm(): void
    {
        $this->status = self::STATUS_CONFIRMED;
        $this->receipt_number = $this->generateReceiptNumber();
        $this->save();

        $this->collection->addActivity('payment_confirmed', 
            "Payment of {$this->amount} confirmed via {$this->payment_method}");
    }

    public function fail(string $reason): void
    {
        $this->status = self::STATUS_FAILED;
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Failed: {$reason}";
        $this->save();

        // Reverse the payment from collection
        $this->collection->amount_collected -= $this->amount;
        $this->collection->updateStatus();
        $this->collection->save();

        $this->collection->addActivity('payment_failed', 
            "Payment of {$this->amount} failed. Reason: {$reason}");
    }

    public function refund(float $amount = null): void
    {
        $refundAmount = $amount ?? $this->amount;
        
        $this->status = self::STATUS_REFUNDED;
        $this->save();

        // Reverse the payment from collection
        $this->collection->amount_collected -= $refundAmount;
        $this->collection->updateStatus();
        $this->collection->save();

        $this->collection->addActivity('payment_refunded', 
            "Payment refund of {$refundAmount} processed");
    }
}
