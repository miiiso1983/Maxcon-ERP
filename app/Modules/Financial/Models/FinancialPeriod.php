<?php

namespace App\Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class FinancialPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_closed',
        'closed_at',
        'closed_by',
        'fiscal_year',
        'period_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    const TYPE_MONTHLY = 'monthly';
    const TYPE_QUARTERLY = 'quarterly';
    const TYPE_YEARLY = 'yearly';

    // Relationships
    public function closedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'closed_by');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return $this->is_closed ? 'danger' : 'success';
    }

    public function getStatusTextAttribute(): string
    {
        return $this->is_closed ? 'Closed' : 'Open';
    }

    public function getDurationAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return 'No dates set';
        }

        $startDate = $this->start_date instanceof \Carbon\Carbon ? $this->start_date : \Carbon\Carbon::parse($this->start_date);
        $endDate = $this->end_date instanceof \Carbon\Carbon ? $this->end_date : \Carbon\Carbon::parse($this->end_date);

        return $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
    }

    // Methods
    public function close(): bool
    {
        if ($this->is_closed) {
            return false;
        }

        // Perform closing entries
        $this->performClosingEntries();

        $this->is_closed = true;
        $this->closed_at = now();
        $this->closed_by = auth()->id();
        $this->save();

        return true;
    }

    public function reopen(): bool
    {
        if (!$this->is_closed) {
            return false;
        }

        // Reverse closing entries
        $this->reverseClosingEntries();

        $this->is_closed = false;
        $this->closed_at = null;
        $this->closed_by = null;
        $this->save();

        return true;
    }

    private function performClosingEntries(): void
    {
        // Close revenue accounts to income summary
        $revenueAccounts = Account::revenue()->get();
        $incomeSummaryAccount = Account::where('account_code', '3900')->first(); // Income Summary

        foreach ($revenueAccounts as $account) {
            if ($account->current_balance != 0) {
                JournalEntry::createEntry([
                    'entry_date' => $this->end_date,
                    'debit_account_id' => $account->id,
                    'credit_account_id' => $incomeSummaryAccount->id,
                    'amount' => abs($account->current_balance),
                    'description' => ['en' => "Closing entry for {$account->account_name}"],
                    'reference' => "CLOSE-{$this->name}",
                    'source_type' => JournalEntry::SOURCE_CLOSING,
                    'source_id' => $this->id,
                ]);
            }
        }

        // Close expense accounts to income summary
        $expenseAccounts = Account::expenses()->get();

        foreach ($expenseAccounts as $account) {
            if ($account->current_balance != 0) {
                JournalEntry::createEntry([
                    'entry_date' => $this->end_date,
                    'debit_account_id' => $incomeSummaryAccount->id,
                    'credit_account_id' => $account->id,
                    'amount' => abs($account->current_balance),
                    'description' => ['en' => "Closing entry for {$account->account_name}"],
                    'reference' => "CLOSE-{$this->name}",
                    'source_type' => JournalEntry::SOURCE_CLOSING,
                    'source_id' => $this->id,
                ]);
            }
        }

        // Close income summary to retained earnings
        $retainedEarningsAccount = Account::where('account_code', '3200')->first(); // Retained Earnings
        
        if ($incomeSummaryAccount->current_balance != 0) {
            if ($incomeSummaryAccount->current_balance > 0) {
                // Profit
                JournalEntry::createEntry([
                    'entry_date' => $this->end_date,
                    'debit_account_id' => $incomeSummaryAccount->id,
                    'credit_account_id' => $retainedEarningsAccount->id,
                    'amount' => $incomeSummaryAccount->current_balance,
                    'description' => ['en' => "Transfer net income to retained earnings"],
                    'reference' => "CLOSE-{$this->name}",
                    'source_type' => JournalEntry::SOURCE_CLOSING,
                    'source_id' => $this->id,
                ]);
            } else {
                // Loss
                JournalEntry::createEntry([
                    'entry_date' => $this->end_date,
                    'debit_account_id' => $retainedEarningsAccount->id,
                    'credit_account_id' => $incomeSummaryAccount->id,
                    'amount' => abs($incomeSummaryAccount->current_balance),
                    'description' => ['en' => "Transfer net loss to retained earnings"],
                    'reference' => "CLOSE-{$this->name}",
                    'source_type' => JournalEntry::SOURCE_CLOSING,
                    'source_id' => $this->id,
                ]);
            }
        }
    }

    private function reverseClosingEntries(): void
    {
        $closingEntries = JournalEntry::where('source_type', JournalEntry::SOURCE_CLOSING)
            ->where('source_id', $this->id)
            ->get();

        foreach ($closingEntries as $entry) {
            $entry->unpost();
            $entry->delete();
        }
    }

    public static function getCurrentPeriod(): ?self
    {
        return self::current()->first();
    }

    public static function createMonthlyPeriods(int $year): array
    {
        $periods = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();

            $periods[] = self::create([
                'name' => $startDate->format('F Y'),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'fiscal_year' => $year,
                'period_type' => self::TYPE_MONTHLY,
            ]);
        }

        return $periods;
    }

    public static function createQuarterlyPeriods(int $year): array
    {
        $periods = [];
        $quarters = [
            ['Q1', 1, 3],
            ['Q2', 4, 6],
            ['Q3', 7, 9],
            ['Q4', 10, 12],
        ];

        foreach ($quarters as [$name, $startMonth, $endMonth]) {
            $startDate = Carbon::create($year, $startMonth, 1);
            $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth();

            $periods[] = self::create([
                'name' => "{$name} {$year}",
                'start_date' => $startDate,
                'end_date' => $endDate,
                'fiscal_year' => $year,
                'period_type' => self::TYPE_QUARTERLY,
            ]);
        }

        return $periods;
    }

    public function canBeClosed(): bool
    {
        if ($this->is_closed) {
            return false;
        }

        // Check if period has ended
        if ($this->end_date->isFuture()) {
            return false;
        }

        return true;
    }

    public function canBeReopened(): bool
    {
        if (!$this->is_closed) {
            return false;
        }

        // Check if there are no subsequent closed periods
        $subsequentClosedPeriods = self::where('start_date', '>', $this->end_date)
            ->where('is_closed', true)
            ->exists();

        return !$subsequentClosedPeriods;
    }
}
