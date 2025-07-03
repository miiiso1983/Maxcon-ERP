<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class CheckTenantLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limitType = null): Response
    {
        // Skip for super admin
        if (Auth::check() && Auth::user()->is_super_admin) {
            return $next($request);
        }

        // Get current tenant
        $tenant = $this->getCurrentTenant();

        if (!$tenant) {
            return redirect()->route('login')->with('error', __('Tenant not found.'));
        }

        // Check if tenant is active
        if (!$tenant->isActive()) {
            return redirect()->route('login')->with('error', __('Your account has been suspended.'));
        }

        // Check if license is valid
        if ($tenant->isExpired()) {
            return redirect()->route('login')->with('error', __('Your license has expired. Please contact support.'));
        }

        // Check specific limits based on the limit type
        if ($limitType) {
            $canProceed = $this->checkSpecificLimit($tenant, $limitType);

            if (!$canProceed) {
                return back()->with('error', $this->getLimitErrorMessage($limitType));
            }
        }

        // Check API limits for API routes
        if ($request->is('api/*')) {
            if (!$tenant->canMakeApiCall()) {
                return response()->json([
                    'error' => 'API call limit exceeded',
                    'message' => 'You have reached your monthly API call limit.'
                ], 429);
            }

            $tenant->incrementApiCalls();
        }

        return $next($request);
    }

    private function getCurrentTenant(): ?Tenant
    {
        // If using tenant() helper from stancl/tenancy
        if (function_exists('tenant')) {
            return tenant();
        }

        // Fallback: get tenant from authenticated user
        if (Auth::check() && Auth::user()->tenant_id) {
            return Tenant::find(Auth::user()->tenant_id);
        }

        return null;
    }

    private function checkSpecificLimit(Tenant $tenant, string $limitType): bool
    {
        return match ($limitType) {
            'users' => $tenant->canAddUser(),
            'warehouses' => $tenant->canAddWarehouse(),
            'products' => $tenant->canAddProduct(),
            'customers' => $tenant->canAddCustomer(),
            'storage' => $tenant->hasStorageSpace(1), // Check for at least 1MB
            'inventory' => $tenant->hasModule('inventory'),
            'accounting' => $tenant->hasModule('accounting'),
            'pos' => $tenant->hasModule('pos'),
            'crm' => $tenant->hasModule('crm'),
            'hrm' => $tenant->hasModule('hrm'),
            'purchasing' => $tenant->hasModule('purchasing'),
            'sales' => $tenant->hasModule('sales'),
            'reporting' => $tenant->hasModule('reporting'),
            'api' => $tenant->hasModule('api'),
            default => true,
        };
    }

    private function getLimitErrorMessage(string $limitType): string
    {
        return match ($limitType) {
            'users' => __('You have reached the maximum number of users allowed for your plan.'),
            'warehouses' => __('You have reached the maximum number of warehouses allowed for your plan.'),
            'products' => __('You have reached the maximum number of products allowed for your plan.'),
            'customers' => __('You have reached the maximum number of customers allowed for your plan.'),
            'storage' => __('You have reached your storage limit. Please upgrade your plan.'),
            'inventory' => __('Inventory module is not enabled for your plan.'),
            'accounting' => __('Accounting module is not enabled for your plan.'),
            'pos' => __('POS module is not enabled for your plan.'),
            'crm' => __('CRM module is not enabled for your plan.'),
            'hrm' => __('HRM module is not enabled for your plan.'),
            'purchasing' => __('Purchasing module is not enabled for your plan.'),
            'sales' => __('Sales module is not enabled for your plan.'),
            'reporting' => __('Reporting module is not enabled for your plan.'),
            'api' => __('API access is not enabled for your plan.'),
            default => __('You have reached a limit for your current plan.'),
        };
    }
}
