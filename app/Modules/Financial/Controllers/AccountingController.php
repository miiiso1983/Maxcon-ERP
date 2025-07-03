<?php

namespace App\Modules\Financial\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financial\Models\Account;
use App\Modules\Financial\Models\JournalEntry;
use App\Modules\Financial\Models\FinancialPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function dashboard()
    {
        $currentPeriod = FinancialPeriod::getCurrentPeriod();

        // Create a default period if none exists
        if (!$currentPeriod) {
            $currentPeriod = new FinancialPeriod([
                'name' => 'Default Period',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'is_closed' => false,
                'period_type' => FinancialPeriod::TYPE_MONTHLY,
                'fiscal_year' => now()->year,
            ]);
        }
        
        // Get account balances by type
        $assets = Account::assets()->sum('current_balance');
        $liabilities = Account::liabilities()->sum('current_balance');
        $equity = Account::equity()->sum('current_balance');
        $revenue = Account::revenue()->sum('current_balance');
        $expenses = Account::expenses()->sum('current_balance');

        // Calculate key metrics
        $netIncome = $revenue - $expenses;
        $totalEquity = $equity + $netIncome;
        $balanceCheck = $assets - ($liabilities + $totalEquity);

        $stats = [
            'total_assets' => $assets,
            'total_liabilities' => $liabilities,
            'total_equity' => $totalEquity,
            'net_income' => $netIncome,
            'balance_check' => $balanceCheck,
            'current_period' => $currentPeriod?->name ?? 'No Period',
            'unposted_entries' => JournalEntry::unposted()->count(),
        ];

        // Recent journal entries
        $recentEntries = JournalEntry::with(['debitAccount', 'creditAccount', 'user'])
            ->latest()
            ->take(10)
            ->get();

        // Account balances summary
        $accountSummary = Account::with('parentAccount')
            ->whereNull('parent_account_id')
            ->orderBy('account_code')
            ->get()
            ->map(function ($account) {
                return [
                    'account' => $account,
                    'balance' => $account->current_balance,
                    'children_balance' => $account->childAccounts->sum('current_balance'),
                ];
            });

        return view('tenant.financial.accounting.dashboard', compact(
            'stats', 'recentEntries', 'accountSummary', 'currentPeriod'
        ));
    }

    public function chartOfAccounts()
    {
        $accounts = Account::getChartOfAccounts();
        
        return view('tenant.financial.accounting.chart-of-accounts', compact('accounts'));
    }

    public function trialBalance(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        
        $accounts = Account::active()
            ->orderBy('account_code')
            ->get()
            ->map(function ($account) use ($asOfDate) {
                $balance = $account->getBalance(new \DateTime($asOfDate));
                return [
                    'account' => $account,
                    'debit_balance' => $account->is_debit_normal && $balance > 0 ? $balance : 0,
                    'credit_balance' => !$account->is_debit_normal && $balance > 0 ? $balance : 0,
                ];
            })
            ->filter(function ($item) {
                return $item['debit_balance'] > 0 || $item['credit_balance'] > 0;
            });

        $totalDebits = $accounts->sum('debit_balance');
        $totalCredits = $accounts->sum('credit_balance');
        $isBalanced = abs($totalDebits - $totalCredits) < 0.01;

        return view('tenant.financial.accounting.trial-balance', compact(
            'accounts', 'totalDebits', 'totalCredits', 'isBalanced', 'asOfDate'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        
        // Assets
        $currentAssets = Account::where('account_type', Account::TYPE_ASSET)
            ->where('account_code', 'like', '11%')
            ->get()
            ->map(function ($account) use ($asOfDate) {
                return [
                    'account' => $account,
                    'balance' => $account->getBalance(new \DateTime($asOfDate)),
                ];
            });

        $fixedAssets = Account::where('account_type', Account::TYPE_ASSET)
            ->where('account_code', 'like', '12%')
            ->get()
            ->map(function ($account) use ($asOfDate) {
                return [
                    'account' => $account,
                    'balance' => $account->getBalance(new \DateTime($asOfDate)),
                ];
            });

        // Liabilities
        $currentLiabilities = Account::where('account_type', Account::TYPE_LIABILITY)
            ->where('account_code', 'like', '21%')
            ->get()
            ->map(function ($account) use ($asOfDate) {
                return [
                    'account' => $account,
                    'balance' => $account->getBalance(new \DateTime($asOfDate)),
                ];
            });

        $longTermLiabilities = Account::where('account_type', Account::TYPE_LIABILITY)
            ->where('account_code', 'like', '22%')
            ->get()
            ->map(function ($account) use ($asOfDate) {
                return [
                    'account' => $account,
                    'balance' => $account->getBalance(new \DateTime($asOfDate)),
                ];
            });

        // Equity
        $equity = Account::equity()
            ->get()
            ->map(function ($account) use ($asOfDate) {
                return [
                    'account' => $account,
                    'balance' => $account->getBalance(new \DateTime($asOfDate)),
                ];
            });

        // Calculate totals
        $totalCurrentAssets = $currentAssets->sum('balance');
        $totalFixedAssets = $fixedAssets->sum('balance');
        $totalAssets = $totalCurrentAssets + $totalFixedAssets;

        $totalCurrentLiabilities = $currentLiabilities->sum('balance');
        $totalLongTermLiabilities = $longTermLiabilities->sum('balance');
        $totalLiabilities = $totalCurrentLiabilities + $totalLongTermLiabilities;

        $totalEquity = $equity->sum('balance');
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return view('tenant.financial.accounting.balance-sheet', compact(
            'currentAssets', 'fixedAssets', 'currentLiabilities', 'longTermLiabilities', 'equity',
            'totalCurrentAssets', 'totalFixedAssets', 'totalAssets',
            'totalCurrentLiabilities', 'totalLongTermLiabilities', 'totalLiabilities',
            'totalEquity', 'totalLiabilitiesAndEquity', 'asOfDate'
        ));
    }

    public function incomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Revenue
        $revenue = Account::revenue()
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                $balance = $this->getAccountBalanceForPeriod($account, $startDate, $endDate);
                return [
                    'account' => $account,
                    'balance' => $balance,
                ];
            });

        // Expenses
        $operatingExpenses = Account::expenses()
            ->where('account_code', 'like', '51%')
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                $balance = $this->getAccountBalanceForPeriod($account, $startDate, $endDate);
                return [
                    'account' => $account,
                    'balance' => $balance,
                ];
            });

        $nonOperatingExpenses = Account::expenses()
            ->where('account_code', 'like', '52%')
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                $balance = $this->getAccountBalanceForPeriod($account, $startDate, $endDate);
                return [
                    'account' => $account,
                    'balance' => $balance,
                ];
            });

        // Calculate totals
        $totalRevenue = $revenue->sum('balance');
        $totalOperatingExpenses = $operatingExpenses->sum('balance');
        $totalNonOperatingExpenses = $nonOperatingExpenses->sum('balance');
        $totalExpenses = $totalOperatingExpenses + $totalNonOperatingExpenses;
        $netIncome = $totalRevenue - $totalExpenses;

        return view('tenant.financial.accounting.income-statement', compact(
            'revenue', 'operatingExpenses', 'nonOperatingExpenses',
            'totalRevenue', 'totalOperatingExpenses', 'totalNonOperatingExpenses',
            'totalExpenses', 'netIncome', 'startDate', 'endDate'
        ));
    }

    private function getAccountBalanceForPeriod(Account $account, string $startDate, string $endDate): float
    {
        $debitTotal = $account->debitEntries()
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_posted', true)
            ->sum('amount');

        $creditTotal = $account->creditEntries()
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_posted', true)
            ->sum('amount');

        if ($account->is_debit_normal) {
            return $debitTotal - $creditTotal;
        } else {
            return $creditTotal - $debitTotal;
        }
    }

    public function generalLedger(Request $request)
    {
        $accountId = $request->get('account_id');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $accounts = Account::active()->orderBy('account_code')->get();
        $selectedAccount = $accountId ? Account::find($accountId) : null;

        $entries = [];
        $runningBalance = 0;

        if ($selectedAccount) {
            // Get opening balance
            $openingBalance = $selectedAccount->getBalance(new \DateTime($startDate));
            $runningBalance = $openingBalance;

            // Get entries for the period
            $journalEntries = JournalEntry::where(function ($query) use ($accountId) {
                $query->where('debit_account_id', $accountId)
                      ->orWhere('credit_account_id', $accountId);
            })
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_posted', true)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

            foreach ($journalEntries as $entry) {
                $isDebit = $entry->debit_account_id == $accountId;
                $amount = $entry->amount;

                if ($selectedAccount->is_debit_normal) {
                    $runningBalance += $isDebit ? $amount : -$amount;
                } else {
                    $runningBalance += $isDebit ? -$amount : $amount;
                }

                $entries[] = [
                    'entry' => $entry,
                    'is_debit' => $isDebit,
                    'debit_amount' => $isDebit ? $amount : 0,
                    'credit_amount' => $isDebit ? 0 : $amount,
                    'running_balance' => $runningBalance,
                ];
            }
        }

        return view('tenant.financial.accounting.general-ledger', compact(
            'accounts', 'selectedAccount', 'entries', 'startDate', 'endDate', 'openingBalance'
        ));
    }

    public function journalEntries(Request $request)
    {
        $query = JournalEntry::with(['debitAccount', 'creditAccount', 'user']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('entry_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('entry_date', '<=', $request->end_date);
        }

        // Filter by account
        if ($request->filled('account_id')) {
            $accountId = $request->account_id;
            $query->where(function ($q) use ($accountId) {
                $q->where('debit_account_id', $accountId)
                  ->orWhere('credit_account_id', $accountId);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_posted', $request->status === 'posted');
        }

        $entries = $query->latest('entry_date')->paginate(20);
        $accounts = Account::active()->orderBy('account_code')->get();

        return view('tenant.financial.accounting.journal-entries', compact('entries', 'accounts'));
    }
}
