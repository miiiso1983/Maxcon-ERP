<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'address',
        'license_key',
        'license_type',
        'license_expires_at',
        'features',
        'status',
        'max_users',
        'current_users',
        'max_warehouses',
        'current_warehouses',
        'max_storage',
        'current_storage',
        'enabled_modules',
        'api_calls_limit',
        'api_calls_used',
        'api_calls_reset_at',
        'max_products',
        'current_products',
        'max_customers',
        'current_customers',
        'admin_user_id',
        'admin_name',
        'admin_email',
        'last_login_at',
        'monthly_fee',
        'next_billing_date',
        'billing_status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'features' => 'array',
        'enabled_modules' => 'array',
        'license_expires_at' => 'datetime',
        'api_calls_reset_at' => 'datetime',
        'last_login_at' => 'datetime',
        'next_billing_date' => 'datetime',
        'monthly_fee' => 'decimal:2',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'address',
            'license_key',
            'license_type',
            'license_expires_at',
            'features',
            'status',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLicenseValid(): bool
    {
        return $this->license_expires_at && $this->license_expires_at->isFuture();
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    public function getAvailableFeatures(): array
    {
        return $this->features ?? [];
    }

    // Relationships
    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    // Limit checking methods
    public function canAddUser(): bool
    {
        return $this->current_users < $this->max_users;
    }

    public function canAddWarehouse(): bool
    {
        return $this->current_warehouses < $this->max_warehouses;
    }

    public function canAddProduct(): bool
    {
        return $this->current_products < $this->max_products;
    }

    public function canAddCustomer(): bool
    {
        return $this->current_customers < $this->max_customers;
    }

    public function hasStorageSpace(int $sizeInMB): bool
    {
        return ($this->current_storage + $sizeInMB) <= $this->max_storage;
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->enabled_modules ?? []);
    }

    // Usage tracking methods
    public function incrementUsers(): void
    {
        $this->increment('current_users');
    }

    public function decrementUsers(): void
    {
        $this->decrement('current_users');
    }

    public function incrementWarehouses(): void
    {
        $this->increment('current_warehouses');
    }

    public function decrementWarehouses(): void
    {
        $this->decrement('current_warehouses');
    }

    public function incrementProducts(): void
    {
        $this->increment('current_products');
    }

    public function decrementProducts(): void
    {
        $this->decrement('current_products');
    }

    public function incrementCustomers(): void
    {
        $this->increment('current_customers');
    }

    public function decrementCustomers(): void
    {
        $this->decrement('current_customers');
    }

    public function addStorageUsage(int $sizeInMB): void
    {
        $this->increment('current_storage', $sizeInMB);
    }

    public function removeStorageUsage(int $sizeInMB): void
    {
        $this->decrement('current_storage', $sizeInMB);
    }

    // API usage tracking
    public function canMakeApiCall(): bool
    {
        if ($this->api_calls_reset_at && $this->api_calls_reset_at->isPast()) {
            $this->resetApiCalls();
        }

        return $this->api_calls_used < $this->api_calls_limit;
    }

    public function incrementApiCalls(): void
    {
        $this->increment('api_calls_used');
    }

    public function resetApiCalls(): void
    {
        $this->update([
            'api_calls_used' => 0,
            'api_calls_reset_at' => now()->addMonth(),
        ]);
    }

    // Status methods
    public function isExpired(): bool
    {
        return $this->license_expires_at && $this->license_expires_at->isPast();
    }

    public function isNearExpiry(int $days = 30): bool
    {
        return $this->license_expires_at &&
               $this->license_expires_at->diffInDays(now()) <= $days;
    }

    public function isBillingOverdue(): bool
    {
        return $this->billing_status === 'overdue';
    }

    // Usage percentage methods
    public function getUsersUsagePercentage(): float
    {
        if ($this->max_users == 0) return 0;
        return ($this->current_users / $this->max_users) * 100;
    }

    public function getWarehousesUsagePercentage(): float
    {
        if ($this->max_warehouses == 0) return 0;
        return ($this->current_warehouses / $this->max_warehouses) * 100;
    }

    public function getStorageUsagePercentage(): float
    {
        if ($this->max_storage == 0) return 0;
        return ($this->current_storage / $this->max_storage) * 100;
    }

    public function getProductsUsagePercentage(): float
    {
        if ($this->max_products == 0) return 0;
        return ($this->current_products / $this->max_products) * 100;
    }

    public function getCustomersUsagePercentage(): float
    {
        if ($this->max_customers == 0) return 0;
        return ($this->current_customers / $this->max_customers) * 100;
    }

    // Available modules
    public static function getAvailableModules(): array
    {
        return [
            'inventory' => 'Inventory Management',
            'accounting' => 'Accounting',
            'pos' => 'Point of Sale',
            'crm' => 'Customer Relationship Management',
            'hrm' => 'Human Resource Management',
            'purchasing' => 'Purchasing',
            'sales' => 'Sales Management',
            'reporting' => 'Advanced Reporting',
            'api' => 'API Access',
            'mobile_app' => 'Mobile App Access',
            'multi_warehouse' => 'Multi-Warehouse',
            'barcode' => 'Barcode Management',
            'loyalty' => 'Customer Loyalty Program',
            'analytics' => 'Advanced Analytics',
            'integrations' => 'Third-party Integrations',
        ];
    }

    // License types
    public static function getLicenseTypes(): array
    {
        return [
            'basic' => [
                'name' => 'Basic',
                'max_users' => 5,
                'max_warehouses' => 1,
                'max_storage' => 500,
                'max_products' => 500,
                'max_customers' => 200,
                'modules' => ['inventory', 'pos', 'sales'],
                'monthly_fee' => 50,
            ],
            'standard' => [
                'name' => 'Standard',
                'max_users' => 15,
                'max_warehouses' => 3,
                'max_storage' => 2000,
                'max_products' => 2000,
                'max_customers' => 1000,
                'modules' => ['inventory', 'pos', 'sales', 'accounting', 'crm'],
                'monthly_fee' => 150,
            ],
            'premium' => [
                'name' => 'Premium',
                'max_users' => 50,
                'max_warehouses' => 10,
                'max_storage' => 10000,
                'max_products' => 10000,
                'max_customers' => 5000,
                'modules' => ['inventory', 'pos', 'sales', 'accounting', 'crm', 'hrm', 'purchasing', 'reporting'],
                'monthly_fee' => 400,
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'max_users' => -1, // Unlimited
                'max_warehouses' => -1,
                'max_storage' => -1,
                'max_products' => -1,
                'max_customers' => -1,
                'modules' => 'all',
                'monthly_fee' => 1000,
            ],
        ];
    }
}
