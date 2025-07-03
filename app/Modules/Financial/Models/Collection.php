<?php

namespace App\Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;
use App\Modules\Customer\Models\Customer;
use App\Modules\Sales\Models\Sale;
use App\Models\User;

class Collection extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = [
        'collection_number',
        'customer_id',
        'collector_id',
        'collection_date',
        'due_date',
        'amount_due',
        'amount_collected',
        'collection_method',
        'status',
        'priority',
        'notes',
        'follow_up_date',
        'contact_attempts',
        'last_contact_date',
        'payment_plan_id',
        'discount_amount',
        'penalty_amount',
        'currency',
        'exchange_rate',
        'meta_data',
    ];

    protected $casts = [
        'collection_date' => 'datetime',
        'due_date' => 'datetime',
        'follow_up_date' => 'datetime',
        'last_contact_date' => 'datetime',
        'amount_due' => 'decimal:2',
        'amount_collected' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'contact_attempts' => 'integer',
        'meta_data' => 'array',
    ];

    public $translatable = ['notes'];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COLLECTED = 'collected';
    const STATUS_PARTIAL = 'partial';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_WRITTEN_OFF = 'written_off';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const METHOD_CASH = 'cash';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CHEQUE = 'cheque';
    const METHOD_CARD = 'card';
    const METHOD_DIGITAL_WALLET = 'digital_wallet';
    const METHOD_PAYMENT_PLAN = 'payment_plan';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'collection_number', 'customer_id', 'amount_due', 
                'amount_collected', 'status', 'priority'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'collection_sales')
            ->withPivot(['amount_allocated'])
            ->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(CollectionPayment::class);
    }

    public function activities()
    {
        return $this->hasMany(CollectionActivity::class);
    }

    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [self::STATUS_COLLECTED, self::STATUS_CANCELLED, self::STATUS_WRITTEN_OFF]);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today())
            ->whereNotIn('status', [self::STATUS_COLLECTED, self::STATUS_CANCELLED, self::STATUS_WRITTEN_OFF]);
    }

    public function scopeFollowUpDue($query)
    {
        return $query->whereDate('follow_up_date', '<=', today())
            ->whereNotIn('status', [self::STATUS_COLLECTED, self::STATUS_CANCELLED, self::STATUS_WRITTEN_OFF]);
    }

    // Accessors & Mutators
    public function getBalanceAmountAttribute(): float
    {
        return $this->amount_due - $this->amount_collected;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() 
            && !in_array($this->status, [self::STATUS_COLLECTED, self::STATUS_CANCELLED, self::STATUS_WRITTEN_OFF]);
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
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COLLECTED => 'success',
            self::STATUS_PARTIAL => 'primary',
            self::STATUS_CANCELLED => 'secondary',
            self::STATUS_WRITTEN_OFF => 'danger',
            default => 'secondary',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'success',
            self::PRIORITY_MEDIUM => 'info',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_URGENT => 'danger',
            default => 'secondary',
        };
    }

    public function getCollectionRateAttribute(): float
    {
        if ($this->amount_due <= 0) {
            return 0;
        }

        return ($this->amount_collected / $this->amount_due) * 100;
    }

    // Methods
    public function generateCollectionNumber(): string
    {
        $prefix = 'COL';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id ?? 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    public function addPayment(float $amount, string $method, ?string $reference = null): CollectionPayment
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'reference' => $reference,
            'user_id' => auth()->id(),
        ]);

        $this->amount_collected += $amount;
        $this->updateStatus();
        $this->save();

        return $payment;
    }

    public function updateStatus(): void
    {
        if ($this->amount_collected <= 0) {
            $this->status = self::STATUS_PENDING;
        } elseif ($this->amount_collected >= $this->amount_due) {
            $this->status = self::STATUS_COLLECTED;
        } else {
            $this->status = self::STATUS_PARTIAL;
        }
    }

    public function addActivity(string $type, string $description, ?array $metadata = null): CollectionActivity
    {
        return $this->activities()->create([
            'activity_type' => $type,
            'description' => $description,
            'activity_date' => now(),
            'user_id' => auth()->id() ?? $this->collector_id ?? 1,
            'metadata' => $metadata,
        ]);
    }

    public function scheduleFollowUp(string $date, string $reason): void
    {
        $this->follow_up_date = $date;
        $this->save();

        $this->addActivity('follow_up_scheduled', "Follow-up scheduled for {$date}. Reason: {$reason}");
    }

    public function markAsContacted(string $method, string $outcome): void
    {
        $this->contact_attempts += 1;
        $this->last_contact_date = now();
        $this->save();

        $this->addActivity('contact_attempt', "Contact via {$method}. Outcome: {$outcome}");
    }

    public function calculatePenalty(): float
    {
        if (!$this->is_overdue) {
            return 0;
        }

        // Calculate penalty based on days overdue (1% per week overdue, max 10%)
        $weeksOverdue = ceil($this->days_overdue / 7);
        $penaltyRate = min($weeksOverdue * 0.01, 0.10);
        
        return $this->amount_due * $penaltyRate;
    }

    public function applyDiscount(float $amount, string $reason): void
    {
        $this->discount_amount = $amount;
        $this->amount_due -= $amount;
        $this->save();

        $this->addActivity('discount_applied', "Discount of {$amount} applied. Reason: {$reason}");
    }

    public function writeOff(string $reason): void
    {
        $this->status = self::STATUS_WRITTEN_OFF;
        $this->save();

        $this->addActivity('written_off', "Collection written off. Reason: {$reason}");
    }

    public function canBeEdited(): bool
    {
        return !in_array($this->status, [self::STATUS_COLLECTED, self::STATUS_WRITTEN_OFF]);
    }

    public function getNextAction(): string
    {
        if ($this->status === self::STATUS_COLLECTED) {
            return 'completed';
        }

        if ($this->follow_up_date && $this->follow_up_date->isPast()) {
            return 'follow_up_due';
        }

        if ($this->is_overdue) {
            if ($this->days_overdue > 30) {
                return 'urgent_collection';
            } elseif ($this->days_overdue > 7) {
                return 'overdue_collection';
            } else {
                return 'contact_customer';
            }
        }

        return 'monitor';
    }
}
