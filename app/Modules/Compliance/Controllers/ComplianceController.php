<?php

namespace App\Modules\Compliance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Compliance\Models\ComplianceItem;
use App\Modules\Compliance\Models\Inspection;
use App\Modules\Compliance\Models\ComplianceViolation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplianceController extends Controller
{
    public function dashboard()
    {
        // Get compliance overview
        $overview = $this->getComplianceOverview();
        
        // Get critical alerts
        $alerts = $this->getCriticalAlerts();
        
        // Get upcoming deadlines
        $upcomingDeadlines = $this->getUpcomingDeadlines();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get compliance trends
        $trends = $this->getComplianceTrends();

        return view('tenant.compliance.dashboard', compact(
            'overview', 'alerts', 'upcomingDeadlines', 'recentActivities', 'trends'
        ));
    }

    public function items()
    {
        $items = ComplianceItem::with('responsiblePerson')
            ->latest()
            ->paginate(20);

        $summary = ComplianceItem::getComplianceDashboard();
        $categories = ComplianceItem::getCategories();
        $types = ComplianceItem::getComplianceTypes();

        return view('tenant.compliance.items.index', compact(
            'items', 'summary', 'categories', 'types'
        ));
    }

    public function createItem()
    {
        $users = User::active()->get();
        $types = ComplianceItem::getComplianceTypes();
        $categories = ComplianceItem::getCategories();
        $priorities = ComplianceItem::getPriorities();
        $riskLevels = ComplianceItem::getRiskLevels();
        
        return view('tenant.compliance.items.create', compact(
            'users', 'types', 'categories', 'priorities', 'riskLevels'
        ));
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'compliance_type' => 'required|in:' . implode(',', array_keys(ComplianceItem::getComplianceTypes())),
            'category' => 'required|in:' . implode(',', array_keys(ComplianceItem::getCategories())),
            'regulatory_body' => 'required|string|max:255',
            'reference_number' => 'required|string|max:100',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'priority' => 'required|in:' . implode(',', array_keys(ComplianceItem::getPriorities())),
            'risk_level' => 'required|in:' . implode(',', array_keys(ComplianceItem::getRiskLevels())),
            'responsible_person_id' => 'required|exists:users,id',
            'cost' => 'sometimes|numeric|min:0',
            'reminder_days' => 'sometimes|integer|min:1|max:365',
        ]);

        DB::beginTransaction();
        try {
            $item = ComplianceItem::create($request->all());
            $item->updateComplianceScore();
            
            // Create initial reminder
            if ($item->expiry_date) {
                $item->createReminder($request->reminder_days ?? 30);
            }

            DB::commit();
            
            return redirect()->route('compliance.items')
                ->with('success', 'Compliance item created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create compliance item: ' . $e->getMessage()]);
        }
    }

    public function showItem(ComplianceItem $item)
    {
        $item->load(['responsiblePerson', 'inspections', 'violations', 'renewals']);
        
        // Get required actions
        $requiredActions = $item->getRequiredActions();
        
        // Get compliance score
        $item->updateComplianceScore();
        
        // Get recent inspections
        $recentInspections = $item->inspections()
            ->latest('actual_date')
            ->take(5)
            ->get();
        
        // Get open violations
        $openViolations = $item->violations()
            ->open()
            ->latest('detected_date')
            ->get();

        return view('tenant.compliance.items.show', compact(
            'item', 'requiredActions', 'recentInspections', 'openViolations'
        ));
    }

    public function renewItem(Request $request, ComplianceItem $item)
    {
        $request->validate([
            'new_expiry_date' => 'required|date|after:today',
            'renewal_date' => 'sometimes|date',
            'cost' => 'sometimes|numeric|min:0',
            'reference_number' => 'sometimes|string|max:100',
            'notes' => 'sometimes|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $renewal = $item->renew($request->all());
            $item->updateComplianceScore();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Compliance item renewed successfully',
                'renewal' => $renewal,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to renew item: ' . $e->getMessage()], 500);
        }
    }

    public function inspections()
    {
        $inspections = Inspection::with(['complianceItem', 'conductedBy'])
            ->latest('scheduled_date')
            ->paginate(20);

        $upcomingCount = Inspection::upcoming(7)->count();
        $overdueCount = Inspection::getOverdueInspections()->count();
        $completedThisMonth = Inspection::completed()
            ->whereMonth('actual_date', now()->month)
            ->count();

        return view('tenant.compliance.inspections.index', compact(
            'inspections', 'upcomingCount', 'overdueCount', 'completedThisMonth'
        ));
    }

    public function createInspection()
    {
        $complianceItems = ComplianceItem::active()->get();
        $users = User::active()->get();
        $types = Inspection::getInspectionTypes();
        
        return view('tenant.compliance.inspections.create', compact(
            'complianceItems', 'users', 'types'
        ));
    }

    public function storeInspection(Request $request)
    {
        $request->validate([
            'compliance_item_id' => 'required|exists:compliance_items,id',
            'inspection_type' => 'required|in:' . implode(',', array_keys(Inspection::getInspectionTypes())),
            'inspector_name' => 'required|string|max:255',
            'inspector_organization' => 'required|string|max:255',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'conducted_by_id' => 'sometimes|exists:users,id',
            'notes' => 'sometimes|string|max:1000',
        ]);

        $inspection = Inspection::create($request->all());

        return redirect()->route('compliance.inspections')
            ->with('success', 'Inspection scheduled successfully');
    }

    public function showInspection(Inspection $inspection)
    {
        $inspection->load(['complianceItem', 'conductedBy', 'approvedBy']);
        
        return view('tenant.compliance.inspections.show', compact('inspection'));
    }

    public function completeInspection(Request $request, Inspection $inspection)
    {
        $request->validate([
            'result' => 'required|in:' . implode(',', array_keys(Inspection::getResults())),
            'score' => 'sometimes|numeric|min:0|max:100',
            'findings' => 'sometimes|array',
            'recommendations' => 'sometimes|array',
            'corrective_actions' => 'sometimes|array',
            'follow_up_required' => 'sometimes|boolean',
            'follow_up_date' => 'sometimes|date|after:today',
            'certificate_issued' => 'sometimes|boolean',
            'certificate_number' => 'sometimes|string|max:100',
            'certificate_expiry' => 'sometimes|date|after:today',
            'next_inspection_date' => 'sometimes|date|after:today',
            'notes' => 'sometimes|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $inspection->complete($request->all());
            
            // Update compliance item score
            $inspection->complianceItem->updateComplianceScore();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Inspection completed successfully',
                'inspection' => $inspection->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to complete inspection: ' . $e->getMessage()], 500);
        }
    }

    public function violations()
    {
        $violations = ComplianceViolation::with(['complianceItem', 'assignedTo'])
            ->latest('detected_date')
            ->paginate(20);

        $openCount = ComplianceViolation::open()->count();
        $criticalCount = ComplianceViolation::critical()->open()->count();
        $overdueCount = ComplianceViolation::getOverdueViolations()->count();

        return view('tenant.compliance.violations.index', compact(
            'violations', 'openCount', 'criticalCount', 'overdueCount'
        ));
    }

    public function createViolation()
    {
        $complianceItems = ComplianceItem::active()->get();
        $users = User::active()->get();
        $types = ComplianceViolation::getViolationTypes();
        $severities = ComplianceViolation::getSeverityLevels();
        
        return view('tenant.compliance.violations.create', compact(
            'complianceItems', 'users', 'types', 'severities'
        ));
    }

    public function storeViolation(Request $request)
    {
        $request->validate([
            'compliance_item_id' => 'required|exists:compliance_items,id',
            'violation_type' => 'required|in:' . implode(',', array_keys(ComplianceViolation::getViolationTypes())),
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'description' => 'required|array',
            'description.en' => 'required|string',
            'severity' => 'required|in:' . implode(',', array_keys(ComplianceViolation::getSeverityLevels())),
            'detected_date' => 'required|date',
            'assigned_to_id' => 'sometimes|exists:users,id',
            'follow_up_required' => 'sometimes|boolean',
            'follow_up_date' => 'sometimes|date|after:today',
        ]);

        $violation = ComplianceViolation::create(array_merge($request->all(), [
            'reported_by_id' => auth()->id(),
            'status' => ComplianceViolation::STATUS_OPEN,
        ]));

        // Update compliance item score
        $violation->complianceItem->updateComplianceScore();

        return redirect()->route('compliance.violations')
            ->with('success', 'Violation reported successfully');
    }

    public function resolveViolation(Request $request, ComplianceViolation $violation)
    {
        $request->validate([
            'resolution_description' => 'required|array',
            'resolution_description.en' => 'required|string',
            'corrective_actions' => 'sometimes|array',
            'preventive_actions' => 'sometimes|array',
            'cost_impact' => 'sometimes|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $violation->resolve($request->all());
            
            // Update compliance item score
            $violation->complianceItem->updateComplianceScore();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Violation resolved successfully',
                'violation' => $violation->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to resolve violation: ' . $e->getMessage()], 500);
        }
    }

    public function reports()
    {
        $period = request('period', 'month');
        
        // Get compliance metrics
        $metrics = $this->getComplianceMetrics($period);
        
        // Get compliance by category
        $categoryBreakdown = $this->getCategoryBreakdown();
        
        // Get risk analysis
        $riskAnalysis = $this->getRiskAnalysis();
        
        // Get trend data
        $trendData = $this->getTrendData($period);

        return view('tenant.compliance.reports.index', compact(
            'metrics', 'categoryBreakdown', 'riskAnalysis', 'trendData', 'period'
        ));
    }

    private function getComplianceOverview(): array
    {
        $dashboard = ComplianceItem::getComplianceDashboard();
        
        return array_merge($dashboard, [
            'upcoming_inspections' => Inspection::upcoming(7)->count(),
            'overdue_inspections' => Inspection::getOverdueInspections()->count(),
            'open_violations' => ComplianceViolation::open()->count(),
            'critical_violations' => ComplianceViolation::critical()->open()->count(),
        ]);
    }

    private function getCriticalAlerts(): array
    {
        $alerts = [];

        // Expired items
        $expiredItems = ComplianceItem::getExpiredItems();
        foreach ($expiredItems as $item) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Expired Compliance',
                'message' => "{$item->title} expired on {$item->expiry_date->format('Y-m-d')}",
                'action_url' => route('compliance.items.show', $item),
                'priority' => 'critical',
            ];
        }

        // Expiring items
        $expiringItems = ComplianceItem::getExpiringItems(30);
        foreach ($expiringItems as $item) {
            $daysLeft = $item->days_until_expiry;
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Expiring Soon',
                'message' => "{$item->title} expires in {$daysLeft} days",
                'action_url' => route('compliance.items.show', $item),
                'priority' => 'high',
            ];
        }

        // Critical violations
        $criticalViolations = ComplianceViolation::getCriticalViolations();
        foreach ($criticalViolations as $violation) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Critical Violation',
                'message' => $violation->title,
                'action_url' => route('compliance.violations.show', $violation),
                'priority' => 'critical',
            ];
        }

        // Overdue inspections
        $overdueInspections = Inspection::getOverdueInspections();
        foreach ($overdueInspections as $inspection) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Overdue Inspection',
                'message' => "Inspection for {$inspection->complianceItem->title} is overdue",
                'action_url' => route('compliance.inspections.show', $inspection),
                'priority' => 'high',
            ];
        }

        // Sort by priority
        usort($alerts, function ($a, $b) {
            $priorities = ['critical' => 3, 'high' => 2, 'medium' => 1, 'low' => 0];
            return ($priorities[$b['priority']] ?? 0) <=> ($priorities[$a['priority']] ?? 0);
        });

        return array_slice($alerts, 0, 10); // Return top 10 alerts
    }

    private function getUpcomingDeadlines(): array
    {
        $deadlines = [];

        // Upcoming expirations
        $expiringItems = ComplianceItem::expiring(60)
            ->orderBy('expiry_date')
            ->take(10)
            ->get();

        foreach ($expiringItems as $item) {
            $deadlines[] = [
                'type' => 'expiration',
                'title' => $item->title,
                'date' => $item->expiry_date,
                'days_left' => $item->days_until_expiry,
                'url' => route('compliance.items.show', $item),
            ];
        }

        // Upcoming inspections
        $upcomingInspections = Inspection::upcoming(30)
            ->orderBy('scheduled_date')
            ->take(10)
            ->get();

        foreach ($upcomingInspections as $inspection) {
            $deadlines[] = [
                'type' => 'inspection',
                'title' => "Inspection: {$inspection->complianceItem->title}",
                'date' => $inspection->scheduled_date,
                'days_left' => now()->diffInDays($inspection->scheduled_date, false),
                'url' => route('compliance.inspections.show', $inspection),
            ];
        }

        // Sort by date
        usort($deadlines, fn($a, $b) => $a['date'] <=> $b['date']);

        return array_slice($deadlines, 0, 15);
    }

    private function getRecentActivities(): array
    {
        $activities = [];

        // Recent compliance items
        $recentItems = ComplianceItem::latest()
            ->take(5)
            ->get();

        foreach ($recentItems as $item) {
            $activities[] = [
                'type' => 'compliance_item',
                'message' => "New compliance item: {$item->title}",
                'date' => $item->created_at,
                'icon' => 'file-alt',
                'color' => 'primary',
            ];
        }

        // Recent inspections
        $recentInspections = Inspection::completed()
            ->latest('actual_date')
            ->take(5)
            ->get();

        foreach ($recentInspections as $inspection) {
            $activities[] = [
                'type' => 'inspection',
                'message' => "Inspection completed: {$inspection->complianceItem->title}",
                'date' => $inspection->actual_date,
                'icon' => 'search',
                'color' => $inspection->result === 'passed' ? 'success' : 'warning',
            ];
        }

        // Recent violations
        $recentViolations = ComplianceViolation::latest('detected_date')
            ->take(5)
            ->get();

        foreach ($recentViolations as $violation) {
            $activities[] = [
                'type' => 'violation',
                'message' => "Violation reported: {$violation->title}",
                'date' => $violation->detected_date,
                'icon' => 'exclamation-triangle',
                'color' => 'danger',
            ];
        }

        // Sort by date
        usort($activities, fn($a, $b) => $b['date'] <=> $a['date']);

        return array_slice($activities, 0, 10);
    }

    private function getComplianceTrends(): array
    {
        $trends = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            
            $trends[] = [
                'month' => $month,
                'total_items' => ComplianceItem::where('created_at', 'like', $month . '%')->count(),
                'expired_items' => ComplianceItem::where('expiry_date', 'like', $month . '%')
                    ->where('expiry_date', '<', now())->count(),
                'violations' => ComplianceViolation::where('detected_date', 'like', $month . '%')->count(),
                'inspections' => Inspection::where('actual_date', 'like', $month . '%')->count(),
            ];
        }

        return $trends;
    }

    private function getComplianceMetrics(string $period): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        return [
            'new_items' => ComplianceItem::where('created_at', '>=', $startDate)->count(),
            'expired_items' => ComplianceItem::where('expiry_date', '>=', $startDate)
                ->where('expiry_date', '<', now())->count(),
            'completed_inspections' => Inspection::completed()
                ->where('actual_date', '>=', $startDate)->count(),
            'resolved_violations' => ComplianceViolation::where('resolution_date', '>=', $startDate)->count(),
            'compliance_score' => $this->calculateOverallComplianceScore(),
        ];
    }

    private function getCategoryBreakdown(): array
    {
        $categories = ComplianceItem::getCategories();
        $breakdown = [];

        foreach ($categories as $key => $name) {
            $total = ComplianceItem::where('category', $key)->count();
            $active = ComplianceItem::where('category', $key)->active()->count();
            $expired = ComplianceItem::where('category', $key)->expired()->count();

            $breakdown[] = [
                'category' => $name,
                'total' => $total,
                'active' => $active,
                'expired' => $expired,
                'compliance_rate' => $total > 0 ? ($active / $total) * 100 : 100,
            ];
        }

        return $breakdown;
    }

    private function getRiskAnalysis(): array
    {
        $riskLevels = ComplianceItem::getRiskLevels();
        $analysis = [];

        foreach ($riskLevels as $key => $name) {
            $total = ComplianceItem::where('risk_level', $key)->count();
            $violations = ComplianceViolation::whereHas('complianceItem', function ($query) use ($key) {
                $query->where('risk_level', $key);
            })->open()->count();

            $analysis[] = [
                'risk_level' => $name,
                'total_items' => $total,
                'open_violations' => $violations,
                'risk_score' => $total > 0 ? ($violations / $total) * 100 : 0,
            ];
        }

        return $analysis;
    }

    private function getTrendData(string $period): array
    {
        $months = $period === 'year' ? 12 : 6;
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            
            $data[] = [
                'period' => $month,
                'compliance_score' => $this->calculateMonthlyComplianceScore($month),
                'new_violations' => ComplianceViolation::where('detected_date', 'like', $month . '%')->count(),
                'resolved_violations' => ComplianceViolation::where('resolution_date', 'like', $month . '%')->count(),
            ];
        }

        return $data;
    }

    private function calculateOverallComplianceScore(): float
    {
        $items = ComplianceItem::all();
        
        if ($items->isEmpty()) {
            return 100;
        }

        $totalScore = $items->sum(function ($item) {
            $item->updateComplianceScore();
            return $item->compliance_score;
        });

        return round($totalScore / $items->count(), 2);
    }

    private function calculateMonthlyComplianceScore(string $month): float
    {
        $items = ComplianceItem::where('created_at', '<=', $month . '-31')->get();
        
        if ($items->isEmpty()) {
            return 100;
        }

        $totalScore = $items->sum('compliance_score');
        return round($totalScore / $items->count(), 2);
    }
}
