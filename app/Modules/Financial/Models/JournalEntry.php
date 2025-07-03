<?php

namespace App\Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;
use App\Models\User;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = [
        'journal_number',
        'entry_date',
        'debit_account_id',
        'credit_account_id',
        'amount',
        'description',
        'reference',
        'source_type',
        'source_id',
        'user_id',
        'is_posted',
        'posted_at',
        'posted_by',
        'currency',
        'exchange_rate',
        'meta_data',
    ];

    protected $casts = [
        'entry_date' => 'datetime',
        'amount' => 'decimal:2',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
        'exchange_rate' => 'decimal:4',
        'meta_data' => 'array',
    ];

    public $translatable = ['description'];

    const SOURCE_MANUAL = 'manual';
    const SOURCE_SALE = 'sale';
    const SOURCE_PURCHASE = 'purchase';
    const SOURCE_PAYMENT = 'payment';
    const SOURCE_COLLECTION = 'collection';
    const SOURCE_ADJUSTMENT = 'adjustment';
    const SOURCE_OPENING_BALANCE = 'opening_balance';
    const SOURCE_CLOSING = 'closing';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'journal_number', 'debit_account_id', 'credit_account_id',
                'amount', 'is_posted'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function debitAccount()
    {
        return $this->belongsTo(Account::class, 'debit_account_id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Account::class, 'credit_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function source()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    public function scopeUnposted($query)
    {
        return $query->where('is_posted', false);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    public function scopeByAccount($query, $accountId)
    {
        return $query->where(function ($q) use ($accountId) {
            $q->where('debit_account_id', $accountId)
              ->orWhere('credit_account_id', $accountId);
        });
    }

    public function scopeBySource($query, string $sourceType, $sourceId = null)
    {
        $query->where('source_type', $sourceType);
        
        if ($sourceId) {
            $query->where('source_id', $sourceId);
        }
        
        return $query;
    }

    // Accessors
    public function getSourceColorAttribute(): string
    {
        return match($this->source_type) {
            self::SOURCE_MANUAL => 'primary',
            self::SOURCE_SALE => 'success',
            self::SOURCE_PURCHASE => 'info',
            self::SOURCE_PAYMENT => 'warning',
            self::SOURCE_COLLECTION => 'secondary',
            self::SOURCE_ADJUSTMENT => 'danger',
            self::SOURCE_OPENING_BALANCE => 'dark',
            self::SOURCE_CLOSING => 'light',
            default => 'secondary',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_posted ? 'success' : 'warning';
    }

    public function getStatusTextAttribute(): string
    {
        return $this->is_posted ? 'Posted' : 'Draft';
    }

    // Methods
    public function generateJournalNumber(): string
    {
        $prefix = 'JE';
        $date = $this->entry_date->format('Ymd');
        $sequence = str_pad($this->id ?? 1, 6, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    public function post(): bool
    {
        if ($this->is_posted) {
            return false;
        }

        // Validate the entry
        if (!$this->isValid()) {
            return false;
        }

        \DB::beginTransaction();
        try {
            // Update account balances
            $this->debitAccount->updateBalance($this->amount, 'debit');
            $this->creditAccount->updateBalance($this->amount, 'credit');

            // Mark as posted
            $this->is_posted = true;
            $this->posted_at = now();
            $this->posted_by = auth()->id();
            $this->save();

            \DB::commit();
            return true;

        } catch (\Exception $e) {
            \DB::rollback();
            return false;
        }
    }

    public function unpost(): bool
    {
        if (!$this->is_posted) {
            return false;
        }

        \DB::beginTransaction();
        try {
            // Reverse account balances
            $this->debitAccount->updateBalance($this->amount, 'credit');
            $this->creditAccount->updateBalance($this->amount, 'debit');

            // Mark as unposted
            $this->is_posted = false;
            $this->posted_at = null;
            $this->posted_by = null;
            $this->save();

            \DB::commit();
            return true;

        } catch (\Exception $e) {
            \DB::rollback();
            return false;
        }
    }

    public function isValid(): bool
    {
        // Check if accounts exist and are active
        if (!$this->debitAccount || !$this->debitAccount->is_active) {
            return false;
        }

        if (!$this->creditAccount || !$this->creditAccount->is_active) {
            return false;
        }

        // Check if amount is positive
        if ($this->amount <= 0) {
            return false;
        }

        // Check if debit and credit accounts are different
        if ($this->debit_account_id === $this->credit_account_id) {
            return false;
        }

        return true;
    }

    public function canBeEdited(): bool
    {
        return !$this->is_posted;
    }

    public function canBeDeleted(): bool
    {
        return !$this->is_posted;
    }

    public static function createEntry(array $data): self
    {
        $entry = new self($data);
        $entry->user_id = auth()->id();
        $entry->save();

        // Generate journal number
        $entry->journal_number = $entry->generateJournalNumber();
        $entry->save();

        return $entry;
    }

    public static function createFromSale(\App\Modules\Sales\Models\Sale $sale): array
    {
        $entries = [];

        // Accounts receivable / Cash (Debit) - Revenue (Credit)
        $receivableAccount = Account::where('account_code', '1200')->first(); // Accounts Receivable
        $cashAccount = Account::where('account_code', '1100')->first(); // Cash
        $revenueAccount = Account::where('account_code', '4100')->first(); // Sales Revenue
        $taxAccount = Account::where('account_code', '2300')->first(); // Sales Tax Payable

        if ($sale->payment_status === \App\Modules\Sales\Models\Sale::PAYMENT_STATUS_PAID) {
            // Cash sale
            $entries[] = self::createEntry([
                'entry_date' => $sale->sale_date,
                'debit_account_id' => $cashAccount->id,
                'credit_account_id' => $revenueAccount->id,
                'amount' => $sale->subtotal,
                'description' => ['en' => "Cash sale - Invoice #{$sale->invoice_number}"],
                'reference' => $sale->invoice_number,
                'source_type' => self::SOURCE_SALE,
                'source_id' => $sale->id,
            ]);
        } else {
            // Credit sale
            $entries[] = self::createEntry([
                'entry_date' => $sale->sale_date,
                'debit_account_id' => $receivableAccount->id,
                'credit_account_id' => $revenueAccount->id,
                'amount' => $sale->subtotal,
                'description' => ['en' => "Credit sale - Invoice #{$sale->invoice_number}"],
                'reference' => $sale->invoice_number,
                'source_type' => self::SOURCE_SALE,
                'source_id' => $sale->id,
            ]);
        }

        // Tax entry if applicable
        if ($sale->tax_amount > 0 && $taxAccount) {
            $entries[] = self::createEntry([
                'entry_date' => $sale->sale_date,
                'debit_account_id' => $sale->payment_status === \App\Modules\Sales\Models\Sale::PAYMENT_STATUS_PAID 
                    ? $cashAccount->id : $receivableAccount->id,
                'credit_account_id' => $taxAccount->id,
                'amount' => $sale->tax_amount,
                'description' => ['en' => "Sales tax - Invoice #{$sale->invoice_number}"],
                'reference' => $sale->invoice_number,
                'source_type' => self::SOURCE_SALE,
                'source_id' => $sale->id,
            ]);
        }

        return $entries;
    }

    public static function createFromPayment(\App\Modules\Sales\Models\Payment $payment): self
    {
        $cashAccount = Account::where('account_code', '1100')->first(); // Cash
        $receivableAccount = Account::where('account_code', '1200')->first(); // Accounts Receivable

        return self::createEntry([
            'entry_date' => $payment->payment_date,
            'debit_account_id' => $cashAccount->id,
            'credit_account_id' => $receivableAccount->id,
            'amount' => $payment->amount,
            'description' => ['en' => "Payment received - {$payment->reference}"],
            'reference' => $payment->reference,
            'source_type' => self::SOURCE_PAYMENT,
            'source_id' => $payment->id,
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            if (empty($entry->journal_number)) {
                $entry->journal_number = 'TEMP-' . time();
            }
        });

        static::created(function ($entry) {
            if (str_starts_with($entry->journal_number, 'TEMP-')) {
                $entry->journal_number = $entry->generateJournalNumber();
                $entry->save();
            }
        });
    }
}
