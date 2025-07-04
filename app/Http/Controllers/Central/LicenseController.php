<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LicenseController extends Controller
{
    /**
     * Display a listing of licenses
     */
    public function index()
    {
        $licenses = Tenant::select([
                'id', 'name', 'email', 'license_key', 'license_type', 
                'license_expires_at', 'status', 'monthly_fee', 'next_billing_date',
                'billing_status', 'created_at'
            ])
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('license_key', 'like', "%{$search}%");
            })
            ->when(request('license_type'), function ($query, $type) {
                $query->where('license_type', $type);
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(request('billing_status'), function ($query, $status) {
                $query->where('billing_status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // License statistics
        $stats = [
            'total_licenses' => Tenant::count(),
            'active_licenses' => Tenant::where('status', 'active')->count(),
            'expired_licenses' => Tenant::where('license_expires_at', '<', now())->count(),
            'expiring_soon' => Tenant::where('license_expires_at', '<=', now()->addDays(30))
                                   ->where('license_expires_at', '>', now())
                                   ->count(),
            'total_revenue' => Tenant::where('billing_status', 'active')->sum('monthly_fee'),
        ];

        // License types
        $licenseTypes = [
            'basic' => 'Basic',
            'standard' => 'Standard', 
            'premium' => 'Premium',
            'enterprise' => 'Enterprise'
        ];

        return view('central.licenses.index', compact('licenses', 'stats', 'licenseTypes'));
    }

    /**
     * Show the form for creating a new license
     */
    public function create()
    {
        $tenants = Tenant::where('status', 'active')->get();
        
        $licenseTypes = [
            'basic' => [
                'name' => 'Basic',
                'price' => 50,
                'features' => ['Inventory Management', 'Basic Sales', 'Customer Management'],
                'limits' => ['max_users' => 5, 'max_warehouses' => 1, 'max_products' => 1000]
            ],
            'standard' => [
                'name' => 'Standard',
                'price' => 100,
                'features' => ['All Basic Features', 'Supplier Management', 'Basic Reports', 'Purchase Orders'],
                'limits' => ['max_users' => 15, 'max_warehouses' => 3, 'max_products' => 5000]
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 200,
                'features' => ['All Standard Features', 'Financial Management', 'HR Module', 'Advanced Reports'],
                'limits' => ['max_users' => 50, 'max_warehouses' => 10, 'max_products' => 20000]
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 500,
                'features' => ['All Premium Features', 'AI Analytics', 'Medical Reps', 'Compliance', 'WhatsApp Integration'],
                'limits' => ['max_users' => 200, 'max_warehouses' => 50, 'max_products' => 100000]
            ]
        ];

        return view('central.licenses.create', compact('tenants', 'licenseTypes'));
    }

    /**
     * Store a newly created license
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'license_type' => 'required|in:basic,standard,premium,enterprise',
            'license_expires_at' => 'required|date|after:today',
            'monthly_fee' => 'required|numeric|min:0',
            'max_users' => 'nullable|integer|min:1',
            'max_warehouses' => 'nullable|integer|min:1',
            'max_products' => 'nullable|integer|min:1',
            'max_customers' => 'nullable|integer|min:1',
            'enabled_modules' => 'nullable|array',
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);

        // Generate new license key
        $licenseKey = $this->generateLicenseKey();

        // Get default limits for license type
        $defaults = $this->getLicenseDefaults($request->license_type);

        $tenant->update([
            'license_key' => $licenseKey,
            'license_type' => $request->license_type,
            'license_expires_at' => $request->license_expires_at,
            'monthly_fee' => $request->monthly_fee,
            'max_users' => $request->max_users ?? $defaults['max_users'],
            'max_warehouses' => $request->max_warehouses ?? $defaults['max_warehouses'],
            'max_products' => $request->max_products ?? $defaults['max_products'],
            'max_customers' => $request->max_customers ?? $defaults['max_customers'],
            'enabled_modules' => $request->enabled_modules ?? $defaults['modules'],
            'billing_status' => 'active',
            'next_billing_date' => now()->addMonth(),
        ]);

        return redirect()->route('central.licenses.index')
            ->with('success', 'License updated successfully.');
    }

    /**
     * Generate a unique license key
     */
    private function generateLicenseKey(): string
    {
        do {
            $key = 'MXC-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (Tenant::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * Get default limits for license type
     */
    private function getLicenseDefaults(string $licenseType): array
    {
        $defaults = [
            'basic' => [
                'max_users' => 5,
                'max_warehouses' => 1,
                'max_products' => 1000,
                'max_customers' => 500,
                'modules' => ['inventory', 'sales', 'customers']
            ],
            'standard' => [
                'max_users' => 15,
                'max_warehouses' => 3,
                'max_products' => 5000,
                'max_customers' => 2000,
                'modules' => ['inventory', 'sales', 'customers', 'suppliers', 'reports']
            ],
            'premium' => [
                'max_users' => 50,
                'max_warehouses' => 10,
                'max_products' => 20000,
                'max_customers' => 10000,
                'modules' => ['inventory', 'sales', 'customers', 'suppliers', 'reports', 'financial', 'hr']
            ],
            'enterprise' => [
                'max_users' => 200,
                'max_warehouses' => 50,
                'max_products' => 100000,
                'max_customers' => 50000,
                'modules' => ['inventory', 'sales', 'customers', 'suppliers', 'reports', 'financial', 'hr', 'medical_reps', 'compliance', 'ai', 'whatsapp']
            ]
        ];

        return $defaults[$licenseType] ?? $defaults['basic'];
    }
}
