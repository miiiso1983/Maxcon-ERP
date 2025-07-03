<?php

namespace App\Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

class Account extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'parent_account_id',
        'description',
        'is_active',
        'is_system',
        'opening_balance',
        'current_balance',
        'currency',
        'tax_account',
        'bank_details',
        'meta_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'tax_account' => 'boolean',
        'bank_details' => 'array',
        'meta_data' => 'array',
    ];

    public $translatable = ['account_name', 'description'];

    const TYPE_ASSET = 'asset';
    const TYPE_LIABILITY = 'liability';
    const TYPE_EQUITY = 'equity';
    const TYPE_REVENUE = 'revenue';
    const TYPE_EXPENSE = 'expense';

    // Asset subtypes
    const SUBTYPE_CURRENT_ASSET = 'current_asset';
    const SUBTYPE_FIXED_ASSET = 'fixed_asset';
    const SUBTYPE_INTANGIBLE_ASSET = 'intangible_asset';

    // Liability subtypes
    const SUBTYPE_CURRENT_LIABILITY = 'current_liability';
    const SUBTYPE_LONG_TERM_LIABILITY = 'long_term_liability';

    // Revenue subtypes
    const SUBTYPE_OPERATING_REVENUE = 'operating_revenue';
    const SUBTYPE_NON_OPERATING_REVENUE = 'non_operating_revenue';

    // Expense subtypes
    const SUBTYPE_OPERATING_EXPENSE = 'operating_expense';
    const SUBTYPE_NON_OPERATING_EXPENSE = 'non_operating_expense';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'account_code', 'account_name', 'account_type', 
                'current_balance', 'is_active'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function childAccounts()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function debitEntries()
    {
        return $this->hasMany(JournalEntry::class, 'debit_account_id');
    }

    public function creditEntries()
    {
        return $this->hasMany(JournalEntry::class, 'credit_account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeAssets($query)
    {
        return $query->where('account_type', self::TYPE_ASSET);
    }

    public function scopeLiabilities($query)
    {
        return $query->where('account_type', self::TYPE_LIABILITY);
    }

    public function scopeEquity($query)
    {
        return $query->where('account_type', self::TYPE_EQUITY);
    }

    public function scopeRevenue($query)
    {
        return $query->where('account_type', self::TYPE_REVENUE);
    }

    public function scopeExpenses($query)
    {
        return $query->where('account_type', self::TYPE_EXPENSE);
    }

    public function scopeParentAccounts($query)
    {
        return $query->whereNull('parent_account_id');
    }

    // Accessors & Mutators
    public function getFullAccountCodeAttribute(): string
    {
        if ($this->parentAccount) {
            return $this->parentAccount->full_account_code . '.' . $this->account_code;
        }
        
        return $this->account_code;
    }

    public function getAccountHierarchyAttribute(): string
    {
        if ($this->parentAccount) {
            return $this->parentAccount->account_hierarchy . ' > ' . $this->account_name;
        }
        
        return $this->account_name;
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->account_type) {
            self::TYPE_ASSET => 'success',
            self::TYPE_LIABILITY => 'danger',
            self::TYPE_EQUITY => 'primary',
            self::TYPE_REVENUE => 'info',
            self::TYPE_EXPENSE => 'warning',
            default => 'secondary',
        };
    }

    public function getBalanceTypeAttribute(): string
    {
        return in_array($this->account_type, [self::TYPE_ASSET, self::TYPE_EXPENSE]) ? 'debit' : 'credit';
    }

    public function getIsDebitNormalAttribute(): bool
    {
        return in_array($this->account_type, [self::TYPE_ASSET, self::TYPE_EXPENSE]);
    }

    // Methods
    public function generateAccountCode(): string
    {
        $typePrefix = match($this->account_type) {
            self::TYPE_ASSET => '1',
            self::TYPE_LIABILITY => '2',
            self::TYPE_EQUITY => '3',
            self::TYPE_REVENUE => '4',
            self::TYPE_EXPENSE => '5',
            default => '9',
        };

        $parentCode = $this->parentAccount ? $this->parentAccount->account_code : $typePrefix . '000';
        $nextNumber = $this->getNextAccountNumber($parentCode);
        
        return $parentCode . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }

    private function getNextAccountNumber(string $parentCode): int
    {
        $lastAccount = Account::where('account_code', 'like', $parentCode . '%')
            ->where('id', '!=', $this->id)
            ->orderBy('account_code', 'desc')
            ->first();

        if (!$lastAccount) {
            return 1;
        }

        $lastNumber = (int) substr($lastAccount->account_code, -2);
        return $lastNumber + 1;
    }

    public function updateBalance(float $amount, string $type): void
    {
        if ($type === 'debit') {
            if ($this->is_debit_normal) {
                $this->current_balance += $amount;
            } else {
                $this->current_balance -= $amount;
            }
        } else { // credit
            if ($this->is_debit_normal) {
                $this->current_balance -= $amount;
            } else {
                $this->current_balance += $amount;
            }
        }

        $this->save();

        // Update parent account balance if exists
        if ($this->parentAccount) {
            $this->parentAccount->updateBalance($amount, $type);
        }
    }

    public function getBalance(?\DateTime $asOfDate = null): float
    {
        if (!$asOfDate) {
            return $this->current_balance;
        }

        // Calculate balance as of specific date
        $debitTotal = $this->debitEntries()
            ->where('entry_date', '<=', $asOfDate)
            ->sum('amount');

        $creditTotal = $this->creditEntries()
            ->where('entry_date', '<=', $asOfDate)
            ->sum('amount');

        if ($this->is_debit_normal) {
            return $this->opening_balance + $debitTotal - $creditTotal;
        } else {
            return $this->opening_balance + $creditTotal - $debitTotal;
        }
    }

    public function getTrialBalanceAmount(): float
    {
        return abs($this->current_balance);
    }

    public function getTrialBalanceSide(): string
    {
        if ($this->current_balance == 0) {
            return 'balanced';
        }

        if ($this->is_debit_normal) {
            return $this->current_balance > 0 ? 'debit' : 'credit';
        } else {
            return $this->current_balance > 0 ? 'credit' : 'debit';
        }
    }

    public function canBeDeleted(): bool
    {
        if ($this->is_system) {
            return false;
        }

        if ($this->journalEntries()->exists()) {
            return false;
        }

        if ($this->childAccounts()->exists()) {
            return false;
        }

        return true;
    }

    public function getAccountPath(): array
    {
        $path = [];
        $current = $this;

        while ($current) {
            array_unshift($path, $current);
            $current = $current->parentAccount;
        }

        return $path;
    }

    public static function getChartOfAccounts(): array
    {
        $accounts = Account::with('childAccounts')
            ->parentAccounts()
            ->orderBy('account_code')
            ->get();

        return $accounts->map(function ($account) {
            return [
                'account' => $account,
                'children' => $account->getChildAccountsRecursive(),
            ];
        })->toArray();
    }

    public function getChildAccountsRecursive(): array
    {
        return $this->childAccounts->map(function ($child) {
            return [
                'account' => $child,
                'children' => $child->getChildAccountsRecursive(),
            ];
        })->toArray();
    }
}
