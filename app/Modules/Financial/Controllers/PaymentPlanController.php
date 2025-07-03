<?php

namespace App\Modules\Financial\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financial\Models\PaymentPlan;
use App\Modules\Customer\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PaymentPlanController extends Controller
{
    public function index(): View
    {
        $paymentPlans = PaymentPlan::with(['customer'])
            ->when(request('search'), function ($query, $search) {
                $query->where('plan_name', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(request('customer_id'), function ($query, $customerId) {
                $query->where('customer_id', $customerId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $customers = Customer::active()->orderBy('name')->get();

        $stats = [
            'total_plans' => PaymentPlan::count(),
            'active_plans' => PaymentPlan::where('status', 'active')->count(),
            'completed_plans' => PaymentPlan::where('status', 'completed')->count(),
            'overdue_plans' => PaymentPlan::where('status', 'overdue')->count(),
        ];

        return view('tenant.financial.payment-plans.index', compact('paymentPlans', 'customers', 'stats'));
    }

    public function create(): View
    {
        $customers = Customer::active()->orderBy('name')->get();
        
        return view('tenant.financial.payment-plans.create', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'plan_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'down_payment' => 'nullable|numeric|min:0',
            'installment_amount' => 'required|numeric|min:0',
            'installment_frequency' => 'required|in:weekly,monthly,quarterly',
            'number_of_installments' => 'required|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'description' => 'nullable|string',
        ]);

        $paymentPlan = PaymentPlan::create([
            'customer_id' => $request->customer_id,
            'plan_name' => $request->plan_name,
            'total_amount' => $request->total_amount,
            'down_payment' => $request->down_payment ?? 0,
            'installment_amount' => $request->installment_amount,
            'installment_frequency' => $request->installment_frequency,
            'number_of_installments' => $request->number_of_installments,
            'start_date' => $request->start_date,
            'description' => $request->description,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('financial.payment-plans.show', $paymentPlan)
            ->with('success', __('Payment plan created successfully.'));
    }

    public function show(PaymentPlan $paymentPlan): View
    {
        $paymentPlan->load(['customer', 'installments']);
        
        return view('tenant.financial.payment-plans.show', compact('paymentPlan'));
    }

    public function edit(PaymentPlan $paymentPlan): View
    {
        $customers = Customer::active()->orderBy('name')->get();
        
        return view('tenant.financial.payment-plans.edit', compact('paymentPlan', 'customers'));
    }

    public function update(Request $request, PaymentPlan $paymentPlan): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'plan_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'down_payment' => 'nullable|numeric|min:0',
            'installment_amount' => 'required|numeric|min:0',
            'installment_frequency' => 'required|in:weekly,monthly,quarterly',
            'number_of_installments' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $paymentPlan->update([
            'customer_id' => $request->customer_id,
            'plan_name' => $request->plan_name,
            'total_amount' => $request->total_amount,
            'down_payment' => $request->down_payment ?? 0,
            'installment_amount' => $request->installment_amount,
            'installment_frequency' => $request->installment_frequency,
            'number_of_installments' => $request->number_of_installments,
            'start_date' => $request->start_date,
            'description' => $request->description,
        ]);

        return redirect()->route('financial.payment-plans.show', $paymentPlan)
            ->with('success', __('Payment plan updated successfully.'));
    }

    public function destroy(PaymentPlan $paymentPlan): RedirectResponse
    {
        if ($paymentPlan->status === 'active') {
            return back()->with('error', __('Cannot delete an active payment plan.'));
        }

        $paymentPlan->delete();

        return redirect()->route('financial.payment-plans.index')
            ->with('success', __('Payment plan deleted successfully.'));
    }

    public function activate(PaymentPlan $paymentPlan): RedirectResponse
    {
        if ($paymentPlan->status !== 'draft') {
            return back()->with('error', __('Only draft payment plans can be activated.'));
        }

        $paymentPlan->update(['status' => 'active']);

        // Generate installments
        $paymentPlan->generateInstallments();

        return back()->with('success', __('Payment plan activated successfully.'));
    }

    public function suspend(PaymentPlan $paymentPlan): RedirectResponse
    {
        if ($paymentPlan->status !== 'active') {
            return back()->with('error', __('Only active payment plans can be suspended.'));
        }

        $paymentPlan->update(['status' => 'suspended']);

        return back()->with('success', __('Payment plan suspended successfully.'));
    }
}
