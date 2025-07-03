<?php

namespace App\Modules\MedicalReps\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MedicalReps\Models\MedicalRep;
use App\Modules\MedicalReps\Models\Territory;
use App\Modules\MedicalReps\Models\CustomerVisit;
use App\Modules\HR\Models\Employee;
use App\Modules\Customer\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicalRepsController extends Controller
{
    public function dashboard()
    {
        // Get overview metrics
        $metrics = $this->getOverviewMetrics();
        
        // Get today's activities
        $todayActivities = $this->getTodayActivities();
        
        // Get performance summary
        $performanceSummary = $this->getPerformanceSummary();
        
        // Get alerts and notifications
        $alerts = $this->getAlerts();

        return view('tenant.medical-reps.dashboard', compact(
            'metrics', 'todayActivities', 'performanceSummary', 'alerts'
        ));
    }

    public function reps()
    {
        $reps = MedicalRep::with(['employee', 'territory', 'supervisor'])
            ->active()
            ->paginate(20);

        $territories = Territory::active()->get();
        $totalReps = MedicalRep::active()->count();
        $topPerformers = MedicalRep::getTopPerformers(5);

        return view('tenant.medical-reps.reps.index', compact(
            'reps', 'territories', 'totalReps', 'topPerformers'
        ));
    }

    public function createRep()
    {
        $employees = Employee::active()
            ->whereDoesntHave('medicalRep')
            ->get();
        $territories = Territory::active()->get();
        $supervisors = MedicalRep::active()->get();
        
        return view('tenant.medical-reps.reps.create', compact(
            'employees', 'territories', 'supervisors'
        ));
    }

    public function storeRep(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id|unique:medical_reps,employee_id',
            'territory_id' => 'required|exists:territories,id',
            'specialization' => 'required|array',
            'license_number' => 'required|string|unique:medical_reps,license_number',
            'license_expiry' => 'required|date|after:today',
            'commission_rate' => 'required|numeric|min:0|max:1',
            'base_salary' => 'required|numeric|min:0',
            'target_monthly' => 'required|numeric|min:0',
            'target_quarterly' => 'required|numeric|min:0',
            'target_annual' => 'required|numeric|min:0',
            'start_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $rep = MedicalRep::create($request->all());
            $rep->rep_code = $rep->generateRepCode();
            $rep->save();

            DB::commit();
            
            return redirect()->route('medical-reps.reps')
                ->with('success', 'Medical representative created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create medical rep: ' . $e->getMessage()]);
        }
    }

    public function showRep(MedicalRep $rep)
    {
        $rep->load(['employee', 'territory', 'supervisor', 'subordinates']);
        
        // Get performance metrics
        $monthlyPerformance = $rep->getPerformanceMetrics('month');
        $quarterlyPerformance = $rep->getPerformanceMetrics('quarter');
        
        // Get visit statistics
        $visitStats = $rep->getVisitStats();
        
        // Get commission details
        $commissionDetails = $rep->calculateMonthlyCommission();
        
        // Get customer portfolio
        $customerPortfolio = $rep->getCustomerPortfolio();
        
        // Get today's schedule
        $todaySchedule = $rep->getTodaySchedule();
        
        // Get upcoming visits
        $upcomingVisits = $rep->getUpcomingVisits();

        return view('tenant.medical-reps.reps.show', compact(
            'rep', 'monthlyPerformance', 'quarterlyPerformance', 'visitStats',
            'commissionDetails', 'customerPortfolio', 'todaySchedule', 'upcomingVisits'
        ));
    }

    public function visits()
    {
        $visits = CustomerVisit::with(['medicalRep.employee', 'customer'])
            ->latest('visit_date')
            ->paginate(20);

        $todayVisits = CustomerVisit::today()->count();
        $completedToday = CustomerVisit::today()->completed()->count();
        $overdueVisits = CustomerVisit::getOverdueVisits()->count();

        return view('tenant.medical-reps.visits.index', compact(
            'visits', 'todayVisits', 'completedToday', 'overdueVisits'
        ));
    }

    public function createVisit()
    {
        $reps = MedicalRep::active()->with('employee')->get();
        $customers = Customer::active()->get();
        $visitTypes = CustomerVisit::getVisitTypes();
        $purposes = CustomerVisit::getPurposes();
        
        return view('tenant.medical-reps.visits.create', compact(
            'reps', 'customers', 'visitTypes', 'purposes'
        ));
    }

    public function storeVisit(Request $request)
    {
        $request->validate([
            'medical_rep_id' => 'required|exists:medical_reps,id',
            'customer_id' => 'required|exists:customers,id',
            'visit_date' => 'required|date|after_or_equal:today',
            'visit_time' => 'required|date_format:H:i',
            'visit_type' => 'required|in:' . implode(',', array_keys(CustomerVisit::getVisitTypes())),
            'purpose' => 'required|in:' . implode(',', array_keys(CustomerVisit::getPurposes())),
            'notes' => 'sometimes|string|max:1000',
        ]);

        $visitDateTime = Carbon::parse($request->visit_date . ' ' . $request->visit_time);
        
        $visit = CustomerVisit::create([
            'medical_rep_id' => $request->medical_rep_id,
            'customer_id' => $request->customer_id,
            'visit_date' => $request->visit_date,
            'visit_time' => $visitDateTime,
            'visit_type' => $request->visit_type,
            'purpose' => $request->purpose,
            'notes' => $request->notes,
            'status' => CustomerVisit::STATUS_PLANNED,
            'is_planned' => true,
            'planned_by' => auth()->user()->medicalRep->id ?? null,
        ]);

        return redirect()->route('medical-reps.visits')
            ->with('success', 'Visit scheduled successfully');
    }

    public function checkInVisit(Request $request, CustomerVisit $visit)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'sometimes|string|max:500',
        ]);

        $location = [
            'lat' => $request->latitude,
            'lng' => $request->longitude,
            'timestamp' => now(),
        ];

        $metadata = [
            'check_in_notes' => $request->notes,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ];

        $visit->checkIn($location, $metadata);

        return response()->json([
            'success' => true,
            'message' => 'Checked in successfully',
            'visit' => $visit->fresh(),
        ]);
    }

    public function checkOutVisit(Request $request, CustomerVisit $visit)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'sometimes|string|max:1000',
            'outcomes' => 'sometimes|array',
            'next_visit_date' => 'sometimes|date|after:today',
        ]);

        $location = [
            'lat' => $request->latitude,
            'lng' => $request->longitude,
            'timestamp' => now(),
        ];

        $metadata = [
            'check_out_notes' => $request->notes,
            'outcomes' => $request->outcomes,
        ];

        $visit->checkOut($location, $metadata);

        if ($request->next_visit_date) {
            $visit->next_visit_date = $request->next_visit_date;
            $visit->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Checked out successfully',
            'visit' => $visit->fresh(),
        ]);
    }

    public function territories()
    {
        $territories = Territory::with(['manager', 'medicalReps'])
            ->withCount(['medicalReps', 'customers'])
            ->get();

        return view('tenant.medical-reps.territories.index', compact('territories'));
    }

    public function showTerritory(Territory $territory)
    {
        $territory->load(['manager', 'medicalReps.employee', 'customers']);
        
        // Get performance metrics
        $performanceMetrics = $territory->getPerformanceMetrics('month');
        
        // Get customer distribution
        $customerDistribution = $territory->getCustomerDistribution();
        
        // Get coverage analysis
        $coverageAnalysis = $territory->getCoverageAnalysis();

        return view('tenant.medical-reps.territories.show', compact(
            'territory', 'performanceMetrics', 'customerDistribution', 'coverageAnalysis'
        ));
    }

    public function performance()
    {
        $period = request('period', 'month');
        
        // Get top performers
        $topPerformers = MedicalRep::getTopPerformers(10, $period);
        
        // Get territory performance
        $territoryPerformance = Territory::active()
            ->get()
            ->map(function ($territory) use ($period) {
                $metrics = $territory->getPerformanceMetrics($period);
                return [
                    'territory' => $territory,
                    'metrics' => $metrics,
                ];
            })
            ->sortByDesc('metrics.total_sales');

        // Get overall metrics
        $overallMetrics = $this->getOverallPerformanceMetrics($period);

        return view('tenant.medical-reps.performance.index', compact(
            'topPerformers', 'territoryPerformance', 'overallMetrics', 'period'
        ));
    }

    public function repPerformance(MedicalRep $rep)
    {
        $periods = ['week', 'month', 'quarter', 'year'];
        $performanceData = [];
        
        foreach ($periods as $period) {
            $performanceData[$period] = $rep->getPerformanceMetrics($period);
        }

        // Get visit trends
        $visitTrends = $this->getVisitTrends($rep);
        
        // Get sales trends
        $salesTrends = $this->getSalesTrends($rep);
        
        // Get commission history
        $commissionHistory = $this->getCommissionHistory($rep);

        return view('tenant.medical-reps.performance.rep', compact(
            'rep', 'performanceData', 'visitTrends', 'salesTrends', 'commissionHistory'
        ));
    }

    public function mobileApp()
    {
        // Mobile app interface for field reps
        $currentUser = auth()->user();
        $rep = MedicalRep::where('employee_id', $currentUser->employee->id ?? null)->first();
        
        if (!$rep) {
            return redirect()->route('medical-reps.dashboard')
                ->with('error', 'You are not assigned as a medical representative');
        }

        $todaySchedule = $rep->getTodaySchedule();
        $upcomingVisits = $rep->getUpcomingVisits(3);
        $recentVisits = $rep->visits()
            ->completed()
            ->latest('visit_date')
            ->take(5)
            ->with('customer')
            ->get();

        return view('tenant.medical-reps.mobile.dashboard', compact(
            'rep', 'todaySchedule', 'upcomingVisits', 'recentVisits'
        ));
    }

    private function getOverviewMetrics(): array
    {
        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m');

        return [
            'total_reps' => MedicalRep::active()->count(),
            'total_territories' => Territory::active()->count(),
            'visits_today' => CustomerVisit::today()->count(),
            'completed_visits_today' => CustomerVisit::today()->completed()->count(),
            'overdue_visits' => CustomerVisit::getOverdueVisits()->count(),
            'monthly_sales' => \App\Modules\Sales\Models\Sale::where('sale_date', 'like', $thisMonth . '%')
                ->whereNotNull('medical_rep_id')
                ->sum('total_amount'),
            'active_customers' => Customer::whereHas('visits', function ($query) {
                $query->where('visit_date', '>=', now()->subDays(30));
            })->count(),
        ];
    }

    private function getTodayActivities(): array
    {
        $today = now()->format('Y-m-d');
        
        return [
            'scheduled_visits' => CustomerVisit::today()->planned()->with(['medicalRep.employee', 'customer'])->get(),
            'in_progress_visits' => CustomerVisit::today()->where('status', CustomerVisit::STATUS_IN_PROGRESS)->with(['medicalRep.employee', 'customer'])->get(),
            'completed_visits' => CustomerVisit::today()->completed()->with(['medicalRep.employee', 'customer'])->get(),
        ];
    }

    private function getPerformanceSummary(): array
    {
        $thisMonth = now()->format('Y-m');
        
        $reps = MedicalRep::active()->get();
        $totalTarget = $reps->sum('target_monthly');
        $totalSales = \App\Modules\Sales\Models\Sale::where('sale_date', 'like', $thisMonth . '%')
            ->whereNotNull('medical_rep_id')
            ->sum('total_amount');

        return [
            'total_target' => $totalTarget,
            'total_sales' => $totalSales,
            'achievement_rate' => $totalTarget > 0 ? ($totalSales / $totalTarget) * 100 : 0,
            'top_performer' => MedicalRep::getTopPerformers(1)->first(),
        ];
    }

    private function getAlerts(): array
    {
        $alerts = [];

        // License expiry alerts
        $expiringLicenses = MedicalRep::active()
            ->where('license_expiry', '<=', now()->addDays(30))
            ->where('license_expiry', '>', now())
            ->with('employee')
            ->get();

        foreach ($expiringLicenses as $rep) {
            $daysLeft = now()->diffInDays($rep->license_expiry);
            $alerts[] = [
                'type' => 'warning',
                'title' => 'License Expiring',
                'message' => "{$rep->full_name}'s license expires in {$daysLeft} days",
                'action_url' => route('medical-reps.reps.show', $rep),
            ];
        }

        // Overdue visits
        $overdueCount = CustomerVisit::getOverdueVisits()->count();
        if ($overdueCount > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Overdue Visits',
                'message' => "{$overdueCount} visits are overdue",
                'action_url' => route('medical-reps.visits', ['filter' => 'overdue']),
            ];
        }

        // Low performance alerts
        $lowPerformers = MedicalRep::active()
            ->get()
            ->filter(function ($rep) {
                $performance = $rep->getPerformanceMetrics('month');
                return $performance['target_achievement'] < 50;
            });

        if ($lowPerformers->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Performance',
                'message' => "{$lowPerformers->count()} reps are below 50% target achievement",
                'action_url' => route('medical-reps.performance.index'),
            ];
        }

        return $alerts;
    }

    private function getOverallPerformanceMetrics(string $period): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $reps = MedicalRep::active()->get();
        $totalSales = 0;
        $totalVisits = 0;
        $completedVisits = 0;
        $totalTarget = 0;

        foreach ($reps as $rep) {
            $metrics = $rep->getPerformanceMetrics($period);
            $totalSales += $metrics['total_sales'];
            $totalVisits += $metrics['visit_count'];
            $completedVisits += $metrics['completed_visits'];
            $totalTarget += $metrics['target'];
        }

        return [
            'total_sales' => $totalSales,
            'total_visits' => $totalVisits,
            'completed_visits' => $completedVisits,
            'total_target' => $totalTarget,
            'target_achievement' => $totalTarget > 0 ? ($totalSales / $totalTarget) * 100 : 0,
            'visit_completion_rate' => $totalVisits > 0 ? ($completedVisits / $totalVisits) * 100 : 0,
            'active_reps' => $reps->count(),
            'average_sales_per_rep' => $reps->count() > 0 ? $totalSales / $reps->count() : 0,
        ];
    }

    private function getVisitTrends(MedicalRep $rep): array
    {
        $trends = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $visits = $rep->visits()
                ->where('visit_date', 'like', $month . '%')
                ->get();

            $trends[] = [
                'month' => $month,
                'total' => $visits->count(),
                'completed' => $visits->where('status', CustomerVisit::STATUS_COMPLETED)->count(),
                'missed' => $visits->where('status', CustomerVisit::STATUS_MISSED)->count(),
            ];
        }

        return $trends;
    }

    private function getSalesTrends(MedicalRep $rep): array
    {
        $trends = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $sales = $rep->sales()
                ->where('sale_date', 'like', $month . '%')
                ->where('status', 'completed')
                ->get();

            $trends[] = [
                'month' => $month,
                'total_sales' => $sales->sum('total_amount'),
                'sales_count' => $sales->count(),
                'target' => $rep->target_monthly,
            ];
        }

        return $trends;
    }

    private function getCommissionHistory(MedicalRep $rep): array
    {
        $history = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $commission = $rep->calculateMonthlyCommission($month);
            $history[] = $commission;
        }

        return $history;
    }
}
