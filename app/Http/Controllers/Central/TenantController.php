<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['adminUser', 'domains'])
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('admin_email', 'like', "%{$search}%");
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(request('license_type'), function ($query, $type) {
                $query->where('license_type', $type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'active')->count(),
            'inactive' => Tenant::where('status', 'inactive')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'expired' => Tenant::whereDate('license_expires_at', '<', now())->count(),
            'expiring_soon' => Tenant::whereBetween('license_expires_at', [now(), now()->addDays(30)])->count(),
        ];

        return view('central.tenants.index-new', compact('tenants', 'stats'));
    }

    public function create()
    {
        $licenseTypes = Tenant::getLicenseTypes();
        $availableModules = Tenant::getAvailableModules();

        return view('central.tenants.create', compact('licenseTypes', 'availableModules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'license_type' => 'required|in:basic,standard,premium,enterprise',
            'license_expires_at' => 'required|date|after:today',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            'enabled_modules' => 'nullable|array',
            'enabled_modules.*' => 'string',
            'max_users' => 'nullable|integer|min:1',
            'max_warehouses' => 'nullable|integer|min:1',
            'max_storage' => 'nullable|integer|min:100',
            'max_products' => 'nullable|integer|min:1',
            'max_customers' => 'nullable|integer|min:1',
            'monthly_fee' => 'nullable|numeric|min:0',
        ]);

        // Get license type defaults
        $licenseDefaults = Tenant::getLicenseTypes()[$request->license_type];

        // Create tenant
        $tenant = Tenant::create([
            'id' => Str::random(8),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'license_key' => Str::random(32),
            'license_type' => $request->license_type,
            'license_expires_at' => $request->license_expires_at,
            'status' => 'active',
            'max_users' => $request->max_users ?? $licenseDefaults['max_users'],
            'max_warehouses' => $request->max_warehouses ?? $licenseDefaults['max_warehouses'],
            'max_storage' => $request->max_storage ?? $licenseDefaults['max_storage'],
            'max_products' => $request->max_products ?? $licenseDefaults['max_products'],
            'max_customers' => $request->max_customers ?? $licenseDefaults['max_customers'],
            'enabled_modules' => $request->enabled_modules ?? $licenseDefaults['modules'],
            'admin_name' => $request->admin_name,
            'admin_email' => $request->admin_email,
            'monthly_fee' => $request->monthly_fee ?? $licenseDefaults['monthly_fee'],
            'next_billing_date' => now()->addMonth(),
            'billing_status' => 'active',
            'api_calls_limit' => 1000,
            'api_calls_reset_at' => now()->addMonth(),
        ]);

        // Create admin user
        $adminUser = User::create([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'tenant_id' => $tenant->id,
            'is_super_admin' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Update tenant with admin user ID
        $tenant->update(['admin_user_id' => $adminUser->id]);

        return redirect()->route('central.tenants.index')
            ->with('success', __('Tenant created successfully. System Administrator can now login with the provided credentials.'));
    }



    public function show(Tenant $tenant)
    {
        $tenant->load('domains');
        return view('central.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('central.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('tenants')->ignore($tenant->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'license_type' => 'required|in:basic,standard,premium,enterprise',
            'license_expires_at' => 'required|date',
            'features' => 'array',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        // Update features based on license type if changed
        if ($validated['license_type'] !== $tenant->license_type) {
            $validated['features'] = $this->getFeaturesForLicenseType($validated['license_type']);
        }

        $tenant->update($validated);

        return redirect()->route('central.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        
        return redirect()->route('central.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }

    private function generateLicenseKey(): string
    {
        do {
            $key = 'MXC-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (Tenant::where('license_key', $key)->exists());

        return $key;
    }

    private function getFeaturesForLicenseType(string $licenseType): array
    {
        $features = [
            'basic' => ['inventory', 'sales', 'customers'],
            'standard' => ['inventory', 'sales', 'customers', 'suppliers', 'reports'],
            'premium' => ['inventory', 'sales', 'customers', 'suppliers', 'reports', 'financial', 'hr'],
            'enterprise' => ['inventory', 'sales', 'customers', 'suppliers', 'reports', 'financial', 'hr', 'medical_reps', 'compliance', 'ai', 'whatsapp'],
        ];

        return $features[$licenseType] ?? $features['basic'];
    }
}
