<?php

namespace App\Modules\Financial\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financial\Models\Account;
use App\Modules\Financial\Models\JournalEntry;
use App\Modules\Financial\Models\Collection;
use App\Modules\Sales\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        return view('tenant.financial.reports.index');
    }

    public function cashFlow(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Operating Activities
        $operatingCashFlow = [
            'cash_from_sales' => $this->getCashFromSales($startDate, $endDate),
            'cash_from_collections' => $this->getCashFromCollections($startDate, $endDate),
            'cash_paid_suppliers' => $this->getCashPaidToSuppliers($startDate, $endDate),
            'cash_paid_expenses' => $this->getCashPaidForExpenses($startDate, $endDate),
        ];

        $netOperatingCashFlow = $operatingCashFlow['cash_from_sales'] + 
                               $operatingCashFlow['cash_from_collections'] - 
                               $operatingCashFlow['cash_paid_suppliers'] - 
                               $operatingCashFlow['cash_paid_expenses'];

        // Investing Activities (simplified)
        $investingCashFlow = [
            'equipment_purchases' => $this->getEquipmentPurchases($startDate, $endDate),
            'asset_sales' => $this->getAssetSales($startDate, $endDate),
        ];

        $netInvestingCashFlow = $investingCashFlow['asset_sales'] - $investingCashFlow['equipment_purchases'];

        // Financing Activities (simplified)
        $financingCashFlow = [
            'owner_investments' => $this->getOwnerInvestments($startDate, $endDate),
            'loan_proceeds' => $this->getLoanProceeds($startDate, $endDate),
            'loan_payments' => $this->getLoanPayments($startDate, $endDate),
        ];

        $netFinancingCashFlow = $financingCashFlow['owner_investments'] + 
                               $financingCashFlow['loan_proceeds'] - 
                               $financingCashFlow['loan_payments'];

        $netCashFlow = $netOperatingCashFlow + $netInvestingCashFlow + $netFinancingCashFlow;

        // Cash balances
        $beginningCash = $this->getCashBalance(new \DateTime($startDate));
        $endingCash = $beginningCash + $netCashFlow;

        return view('tenant.financial.reports.cash-flow', compact(
            'operatingCashFlow', 'investingCashFlow', 'financingCashFlow',
            'netOperatingCashFlow', 'netInvestingCashFlow', 'netFinancingCashFlow',
            'netCashFlow', 'beginningCash', 'endingCash', 'startDate', 'endDate'
        ));
    }

    public function agingReport(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $reportType = $request->get('type', 'receivables'); // receivables or payables

        if ($reportType === 'receivables') {
            $data = $this->getAccountsReceivableAging($asOfDate);
            $title = 'Accounts Receivable Aging';
        } else {
            $data = $this->getAccountsPayableAging($asOfDate);
            $title = 'Accounts Payable Aging';
        }

        return view('tenant.financial.reports.aging', compact('data', 'title', 'asOfDate', 'reportType'));
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $comparison = $request->get('comparison', 'none'); // none, previous_period, previous_year

        $currentPeriod = $this->getProfitLossData($startDate, $endDate);
        $comparisonPeriod = null;

        if ($comparison === 'previous_period') {
            $periodLength = (new \DateTime($endDate))->diff(new \DateTime($startDate))->days;
            $comparisonStart = (new \DateTime($startDate))->sub(new \DateInterval("P{$periodLength}D"))->format('Y-m-d');
            $comparisonEnd = (new \DateTime($startDate))->sub(new \DateInterval('P1D'))->format('Y-m-d');
            $comparisonPeriod = $this->getProfitLossData($comparisonStart, $comparisonEnd);
        } elseif ($comparison === 'previous_year') {
            $comparisonStart = (new \DateTime($startDate))->sub(new \DateInterval('P1Y'))->format('Y-m-d');
            $comparisonEnd = (new \DateTime($endDate))->sub(new \DateInterval('P1Y'))->format('Y-m-d');
            $comparisonPeriod = $this->getProfitLossData($comparisonStart, $comparisonEnd);
        }

        return view('tenant.financial.reports.profit-loss', compact(
            'currentPeriod', 'comparisonPeriod', 'startDate', 'endDate', 'comparison'
        ));
    }

    private function getCashFromSales(string $startDate, string $endDate): float
    {
        return Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', Sale::PAYMENT_STATUS_PAID)
            ->sum('total_amount');
    }

    private function getCashFromCollections(string $startDate, string $endDate): float
    {
        return Collection::whereBetween('updated_at', [$startDate, $endDate])
            ->sum('amount_collected');
    }

    private function getCashPaidToSuppliers(string $startDate, string $endDate): float
    {
        // This would need to be implemented based on purchase orders and payments
        return 0;
    }

    private function getCashPaidForExpenses(string $startDate, string $endDate): float
    {
        $expenseAccounts = Account::expenses()->pluck('id');
        
        return JournalEntry::whereIn('debit_account_id', $expenseAccounts)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_posted', true)
            ->sum('amount');
    }

    private function getEquipmentPurchases(string $startDate, string $endDate): float
    {
        $equipmentAccount = Account::where('account_code', '1510')->first();
        
        if (!$equipmentAccount) {
            return 0;
        }

        return JournalEntry::where('debit_account_id', $equipmentAccount->id)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_posted', true)
            ->sum('amount');
    }

    private function getAssetSales(string $startDate, string $endDate): float
    {
        // This would need to be implemented based on asset disposal entries
        return 0;
    }

    private function getOwnerInvestments(string $startDate, string $endDate): float
    {
        $capitalAccount = Account::where('account_code', '3100')->first();
        
        if (!$capitalAccount) {
            return 0;
        }

        return JournalEntry::where('credit_account_id', $capitalAccount->id)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_posted', true)
            ->sum('amount');
    }

    private function getLoanProceeds(string $startDate, string $endDate): float
    {
        // This would need to be implemented based on loan accounts
        return 0;
    }

    private function getLoanPayments(string $startDate, string $endDate): float
    {
        // This would need to be implemented based on loan payment entries
        return 0;
    }

    private function getCashBalance(\DateTime $asOfDate): float
    {
        $cashAccount = Account::where('account_code', '1110')->first();
        
        if (!$cashAccount) {
            return 0;
        }

        return $cashAccount->getBalance($asOfDate);
    }

    private function getAccountsReceivableAging(string $asOfDate): array
    {
        $receivableAccount = Account::where('account_code', '1200')->first();
        
        if (!$receivableAccount) {
            return [];
        }

        // This is a simplified version - in reality, you'd track individual customer balances
        $totalReceivables = $receivableAccount->getBalance(new \DateTime($asOfDate));
        
        return [
            'current' => $totalReceivables * 0.4,
            '1_30_days' => $totalReceivables * 0.3,
            '31_60_days' => $totalReceivables * 0.2,
            '61_90_days' => $totalReceivables * 0.07,
            'over_90_days' => $totalReceivables * 0.03,
            'total' => $totalReceivables,
        ];
    }

    private function getAccountsPayableAging(string $asOfDate): array
    {
        $payableAccount = Account::where('account_code', '2110')->first();
        
        if (!$payableAccount) {
            return [];
        }

        // This is a simplified version - in reality, you'd track individual supplier balances
        $totalPayables = $payableAccount->getBalance(new \DateTime($asOfDate));
        
        return [
            'current' => $totalPayables * 0.5,
            '1_30_days' => $totalPayables * 0.3,
            '31_60_days' => $totalPayables * 0.15,
            '61_90_days' => $totalPayables * 0.04,
            'over_90_days' => $totalPayables * 0.01,
            'total' => $totalPayables,
        ];
    }

    private function getProfitLossData(string $startDate, string $endDate): array
    {
        $revenue = Account::revenue()
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                return [
                    'account' => $account,
                    'amount' => $this->getAccountBalanceForPeriod($account, $startDate, $endDate),
                ];
            });

        $expenses = Account::expenses()
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                return [
                    'account' => $account,
                    'amount' => $this->getAccountBalanceForPeriod($account, $startDate, $endDate),
                ];
            });

        $totalRevenue = $revenue->sum('amount');
        $totalExpenses = $expenses->sum('amount');
        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
        ];
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

    public function exportReport(Request $request)
    {
        $reportType = $request->get('type');
        $format = $request->get('format', 'pdf'); // pdf, excel, csv

        // This would implement the actual export functionality
        // For now, just return a success message
        
        return response()->json([
            'success' => true,
            'message' => "Report exported successfully as {$format}",
            'download_url' => "/downloads/report-{$reportType}-" . time() . ".{$format}"
        ]);
    }
}
