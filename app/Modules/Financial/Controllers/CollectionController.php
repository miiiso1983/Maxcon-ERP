<?php

namespace App\Modules\Financial\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financial\Models\Collection;
use App\Modules\Customer\Models\Customer;
use App\Modules\Sales\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Collection::with(['customer', 'collector']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('collection_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name->en', 'like', "%{$search}%")
                                   ->orWhere('name->ar', 'like', "%{$search}%")
                                   ->orWhere('name->ku', 'like', "%{$search}%")
                                   ->orWhere('customer_code', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by collector
        if ($request->filled('collector_id')) {
            $query->where('collector_id', $request->collector_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        // Special filters
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'overdue':
                    $query->overdue();
                    break;
                case 'due_today':
                    $query->dueToday();
                    break;
                case 'follow_up':
                    $query->followUpDue();
                    break;
            }
        }

        $collections = $query->latest('due_date')->paginate(20);

        // Get summary statistics
        $stats = [
            'total_collections' => Collection::count(),
            'pending_amount' => Collection::whereIn('status', ['pending', 'in_progress', 'partial'])->sum('amount_due'),
            'collected_amount' => Collection::sum('amount_collected'),
            'overdue_count' => Collection::overdue()->count(),
            'overdue_amount' => Collection::overdue()->sum(\DB::raw('amount_due - amount_collected')),
        ];

        // Get collectors for filter
        $collectors = \App\Models\User::whereHas('collections')->get();

        return view('tenant.financial.collections.index', compact('collections', 'stats', 'collectors'));
    }

    public function create()
    {
        $customers = Customer::active()->get();
        $collectors = \App\Models\User::all();
        
        // Get customers with outstanding sales
        $outstandingSales = Sale::with('customer')
            ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->get()
            ->groupBy('customer_id');

        return view('tenant.financial.collections.create', compact('customers', 'collectors', 'outstandingSales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'collector_id' => 'nullable|exists:users,id',
            'amount_due' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'collection_method' => 'required|in:cash,bank_transfer,cheque,card,digital_wallet,payment_plan',
            'notes.en' => 'nullable|string',
            'notes.ar' => 'nullable|string',
            'notes.ku' => 'nullable|string',
            'sales' => 'nullable|array',
            'sales.*' => 'exists:sales,id',
            'follow_up_date' => 'nullable|date|after:today',
        ]);

        DB::beginTransaction();
        try {
            $collection = Collection::create(array_merge($validated, [
                'collection_date' => now(),
                'status' => Collection::STATUS_PENDING,
            ]));

            // Generate collection number
            $collection->collection_number = $collection->generateCollectionNumber();
            $collection->save();

            // Link to sales if provided
            if (!empty($validated['sales'])) {
                foreach ($validated['sales'] as $saleId) {
                    $sale = Sale::find($saleId);
                    $balanceAmount = $sale->total_amount - $sale->paid_amount;
                    
                    $collection->sales()->attach($saleId, [
                        'amount_allocated' => $balanceAmount
                    ]);
                }
            }

            // Add initial activity
            $collection->addActivity('collection_created', 'Collection record created');

            DB::commit();

            return redirect()->route('collections.show', $collection)
                ->with('success', 'Collection created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating collection: ' . $e->getMessage());
        }
    }

    public function show(Collection $collection)
    {
        $collection->load([
            'customer',
            'collector',
            'sales',
            'payments.user',
            'activities.user',
            'paymentPlan'
        ]);

        return view('tenant.financial.collections.show', compact('collection'));
    }

    public function edit(Collection $collection)
    {
        if (!$collection->canBeEdited()) {
            return redirect()->back()
                ->with('error', 'This collection cannot be edited.');
        }

        $customers = Customer::active()->get();
        $collectors = \App\Models\User::all();

        return view('tenant.financial.collections.edit', compact('collection', 'customers', 'collectors'));
    }

    public function update(Request $request, Collection $collection)
    {
        if (!$collection->canBeEdited()) {
            return redirect()->back()
                ->with('error', 'This collection cannot be edited.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'collector_id' => 'nullable|exists:users,id',
            'amount_due' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'collection_method' => 'required|in:cash,bank_transfer,cheque,card,digital_wallet,payment_plan',
            'notes.en' => 'nullable|string',
            'notes.ar' => 'nullable|string',
            'notes.ku' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:today',
        ]);

        $collection->update($validated);

        $collection->addActivity('collection_updated', 'Collection details updated');

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Collection updated successfully.');
    }

    public function addPayment(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $collection->balance_amount,
            'payment_method' => 'required|in:cash,bank_transfer,cheque,card,digital_wallet',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment = $collection->addPayment(
            $validated['amount'],
            $validated['payment_method'],
            $validated['reference']
        );

        if (!empty($validated['notes'])) {
            $payment->notes = $validated['notes'];
            $payment->save();
        }

        return redirect()->back()
            ->with('success', 'Payment added successfully.');
    }

    public function addActivity(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'activity_type' => 'required|string',
            'description' => 'required|string',
            'follow_up_date' => 'nullable|date|after:today',
        ]);

        $collection->addActivity(
            $validated['activity_type'],
            $validated['description']
        );

        if (!empty($validated['follow_up_date'])) {
            $collection->follow_up_date = $validated['follow_up_date'];
            $collection->save();
        }

        return redirect()->back()
            ->with('success', 'Activity added successfully.');
    }

    public function markAsContacted(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'contact_method' => 'required|string',
            'outcome' => 'required|string',
            'follow_up_date' => 'nullable|date|after:today',
        ]);

        $collection->markAsContacted(
            $validated['contact_method'],
            $validated['outcome']
        );

        if (!empty($validated['follow_up_date'])) {
            $collection->follow_up_date = $validated['follow_up_date'];
            $collection->save();
        }

        return redirect()->back()
            ->with('success', 'Contact recorded successfully.');
    }

    public function applyDiscount(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'discount_amount' => 'required|numeric|min:0.01|max:' . $collection->balance_amount,
            'reason' => 'required|string|max:255',
        ]);

        $collection->applyDiscount(
            $validated['discount_amount'],
            $validated['reason']
        );

        return redirect()->back()
            ->with('success', 'Discount applied successfully.');
    }

    public function writeOff(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $collection->writeOff($validated['reason']);

        return redirect()->route('collections.index')
            ->with('success', 'Collection written off successfully.');
    }

    public function dashboard()
    {
        $stats = [
            'total_outstanding' => Collection::whereIn('status', ['pending', 'in_progress', 'partial'])->sum('amount_due'),
            'collected_today' => Collection::whereDate('updated_at', today())->sum('amount_collected'),
            'overdue_amount' => Collection::overdue()->sum(\DB::raw('amount_due - amount_collected')),
            'follow_ups_due' => Collection::followUpDue()->count(),
        ];

        $recentCollections = Collection::with(['customer', 'collector'])
            ->latest()
            ->take(10)
            ->get();

        $overdueCollections = Collection::with(['customer', 'collector'])
            ->overdue()
            ->orderBy('due_date')
            ->take(10)
            ->get();

        $followUpsDue = Collection::with(['customer', 'collector'])
            ->followUpDue()
            ->orderBy('follow_up_date')
            ->take(10)
            ->get();

        return view('tenant.financial.collections.dashboard', compact(
            'stats', 'recentCollections', 'overdueCollections', 'followUpsDue'
        ));
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:assign_collector,update_priority,schedule_follow_up',
            'collections' => 'required|array',
            'collections.*' => 'exists:collections,id',
            'collector_id' => 'required_if:action,assign_collector|exists:users,id',
            'priority' => 'required_if:action,update_priority|in:low,medium,high,urgent',
            'follow_up_date' => 'required_if:action,schedule_follow_up|date|after:today',
        ]);

        $collections = Collection::whereIn('id', $request->collections);

        switch ($request->action) {
            case 'assign_collector':
                $collections->update(['collector_id' => $request->collector_id]);
                $message = 'Collector assigned successfully.';
                break;
            case 'update_priority':
                $collections->update(['priority' => $request->priority]);
                $message = 'Priority updated successfully.';
                break;
            case 'schedule_follow_up':
                $collections->update(['follow_up_date' => $request->follow_up_date]);
                $message = 'Follow-up scheduled successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}
