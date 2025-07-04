<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\TenantController;
use App\Modules\Inventory\Controllers\InventoryController;
use App\Modules\Inventory\Controllers\ProductController;
use App\Modules\Reports\Controllers\AnalyticsController;

/*
|--------------------------------------------------------------------------
| Central App Routes
|--------------------------------------------------------------------------
|
| Here you can register routes for the central application.
| These routes are for super admin functionality and tenant management.
|
*/

Route::get('/', function () {
    // If user is authenticated and is super admin, redirect to master admin dashboard
    if (auth()->check() && auth()->user()->is_super_admin) {
        return redirect()->route('central.dashboard');
    }

    return view('central.welcome');
})->name('central.home');

// Force tenant login route to take priority over default Laravel auth
Route::get('/login', function () {
    return view('tenant.auth.login');
})->middleware('guest')->name('login');

// Ensure login POST route works (backup to auth.php)
Route::post('/login', function (\Illuminate\Http\Request $request) {
    try {
        // Simple direct authentication without complex validation
        $email = $request->input('email');
        $password = $request->input('password');

        if (!$email || !$password) {
            return back()->withErrors(['email' => 'Email and password are required.']);
        }

        // Find user and verify password manually to avoid session issues
        $user = \App\Models\User::where('email', $email)->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            // Manual login without Auth::attempt to avoid session issues
            Auth::login($user, $request->boolean('remember'));

            // Check if user is super admin
            if ($user->is_super_admin ?? false) {
                return redirect('/master-admin/dashboard');
            }

            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');

    } catch (\Exception $e) {
        // Log the error and return a generic message
        \Illuminate\Support\Facades\Log::error('Login error: ' . $e->getMessage());
        return back()->withErrors(['email' => 'Login failed. Please try again.']);
    }
})->middleware('guest')->name('login.post');

// Dashboard route for authenticated users
Route::get('/dashboard', function () {
    // If user is super admin, redirect to master admin dashboard
    if (auth()->check() && auth()->user()->is_super_admin) {
        return redirect()->route('central.dashboard');
    }

    // For regular users, show tenant dashboard
    return view('tenant.dashboard');
})->middleware('auth')->name('dashboard');

// Tenant dashboard route for central domain access
Route::get('/tenant-dashboard', function () {
    return view('tenant.dashboard');
})->middleware('auth')->name('tenant.dashboard');

// Master Admin Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/master-admin/login', [\App\Http\Controllers\Central\AuthController::class, 'showLoginForm'])->name('central.login');
    Route::post('/master-admin/login', [\App\Http\Controllers\Central\AuthController::class, 'login'])->name('central.login.post');
});

// Master Admin Dashboard Routes
Route::middleware(['auth', 'master_admin'])->prefix('master-admin')->name('central.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [\App\Http\Controllers\Central\AuthController::class, 'logout'])->name('logout');

    // Tenant Management
    Route::resource('tenants', TenantController::class);

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Central\UserController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Central\UserController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Central\UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [\App\Http\Controllers\Central\UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [\App\Http\Controllers\Central\UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [\App\Http\Controllers\Central\UserController::class, 'destroy'])->name('destroy');
    });

    // System Management
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/info', [\App\Http\Controllers\Central\SystemController::class, 'info'])->name('info');
        Route::get('/logs', [\App\Http\Controllers\Central\SystemController::class, 'logs'])->name('logs');
        Route::post('/cache/clear', [\App\Http\Controllers\Central\SystemController::class, 'clearCache'])->name('cache.clear');
        Route::post('/maintenance/enable', [\App\Http\Controllers\Central\SystemController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [\App\Http\Controllers\Central\SystemController::class, 'disableMaintenance'])->name('maintenance.disable');
    });

    // License Management
    Route::prefix('licenses')->name('licenses.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Central\LicenseController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Central\LicenseController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Central\LicenseController::class, 'store'])->name('store');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Central\SettingsController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Central\SettingsController::class, 'update'])->name('update');
    });
});

// Temporary test routes (remove when tenant system is fully working)
Route::get('/test-inventory', [InventoryController::class, 'index'])->name('test.inventory');
Route::get('/test-sales', [\App\Modules\Sales\Controllers\SalesController::class, 'index'])->name('test.sales');
Route::get('/test-suppliers', [\App\Modules\Supplier\Controllers\SupplierController::class, 'index'])->name('test.suppliers');
Route::get('/test-collections', [\App\Modules\Financial\Controllers\CollectionController::class, 'index'])->name('test.collections');
Route::get('/test-accounting', [\App\Modules\Financial\Controllers\AccountingController::class, 'dashboard'])->name('test.accounting');
Route::get('/test-reports', [\App\Modules\Reports\Controllers\ReportsController::class, 'dashboard'])->name('test.reports');

// Reports routes for central domain
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [\App\Modules\Reports\Controllers\ReportsController::class, 'index'])->name('index');
    Route::get('/dashboard', [\App\Modules\Reports\Controllers\ReportsController::class, 'dashboard'])->name('dashboard');
    Route::get('/create', [\App\Modules\Reports\Controllers\ReportsController::class, 'create'])->name('create');
    Route::post('/', [\App\Modules\Reports\Controllers\ReportsController::class, 'store'])->name('store');

    // Specific Reports
    Route::get('/sales', [\App\Modules\Reports\Controllers\ReportsController::class, 'salesReport'])->name('sales');
    Route::get('/inventory', [\App\Modules\Reports\Controllers\ReportsController::class, 'inventoryReport'])->name('inventory');
    Route::get('/financial', [\App\Modules\Reports\Controllers\ReportsController::class, 'financialReport'])->name('financial');
    Route::get('/customers', [\App\Modules\Reports\Controllers\ReportsController::class, 'customersReport'])->name('customers');
    Route::get('/suppliers', [\App\Modules\Reports\Controllers\ReportsController::class, 'suppliersReport'])->name('suppliers');
    Route::get('/analytics', [\App\Modules\Reports\Controllers\AnalyticsController::class, 'index'])->name('analytics');
});

// Medical Reps routes for central domain
Route::prefix('medical-reps')->name('medical-reps.')->group(function () {
    Route::get('/', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'dashboard'])->name('dashboard');

    // Representatives Management
    Route::prefix('reps')->name('reps.')->group(function () {
        Route::get('/', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'reps'])->name('index');
        Route::get('/create', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'createRep'])->name('create');
        Route::post('/', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'storeRep'])->name('store');
        Route::get('/{rep}', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'showRep'])->name('show');
        Route::get('/{rep}/edit', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'editRep'])->name('edit');
        Route::put('/{rep}', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'updateRep'])->name('update');
    });

    // Visit Management
    Route::prefix('visits')->name('visits.')->group(function () {
        Route::get('/', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'visits'])->name('index');
        Route::get('/create', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'createVisit'])->name('create');
        Route::post('/', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'storeVisit'])->name('store');
        Route::post('/{visit}/check-in', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'checkInVisit'])->name('check-in');
        Route::post('/{visit}/check-out', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'checkOutVisit'])->name('check-out');
    });

    // Territory Management
    Route::prefix('territories')->name('territories.')->group(function () {
        Route::get('/', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'territories'])->name('index');
        Route::get('/{territory}', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'showTerritory'])->name('show');
    });

    // Performance & Analytics
    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'performance'])->name('index');
        Route::get('/rep/{rep}', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'repPerformance'])->name('rep');
    });

    // Mobile App Interface
    Route::get('/mobile', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'mobileApp'])->name('mobile');
});

// Inventory routes for central domain
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [\App\Modules\Inventory\Controllers\InventoryController::class, 'index'])->name('index');
    Route::get('/low-stock', [\App\Modules\Inventory\Controllers\InventoryController::class, 'lowStock'])->name('low-stock');
    Route::get('/expiring', [\App\Modules\Inventory\Controllers\InventoryController::class, 'expiring'])->name('expiring');
    Route::get('/movements', [\App\Modules\Inventory\Controllers\InventoryController::class, 'stockMovements'])->name('movements');
    Route::post('/products/{product}/adjust-stock', [\App\Modules\Inventory\Controllers\InventoryController::class, 'adjustStock'])->name('products.adjust-stock');

    // Product Management
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [\App\Modules\Inventory\Controllers\ProductController::class, 'index'])->name('index');
        Route::get('/create', [\App\Modules\Inventory\Controllers\ProductController::class, 'create'])->name('create');
        Route::post('/', [\App\Modules\Inventory\Controllers\ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [\App\Modules\Inventory\Controllers\ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [\App\Modules\Inventory\Controllers\ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [\App\Modules\Inventory\Controllers\ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [\App\Modules\Inventory\Controllers\ProductController::class, 'destroy'])->name('destroy');
        Route::post('/import', [\App\Modules\Inventory\Controllers\ProductController::class, 'import'])->name('import');
        Route::get('/export', [\App\Modules\Inventory\Controllers\ProductController::class, 'export'])->name('export');
        Route::post('/bulk-action', [\App\Modules\Inventory\Controllers\ProductController::class, 'bulkAction'])->name('bulk-action');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', fn() => view('tenant.inventory.categories.index'))->name('index');
    });

    // Warehouses
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', fn() => view('tenant.inventory.warehouses.index'))->name('index');
    });
});
Route::get('/test-ai', [\App\Modules\AI\Controllers\AIController::class, 'dashboard'])->name('test.ai');
Route::get('/test-hr', [\App\Modules\HR\Controllers\HRController::class, 'dashboard'])->name('test.hr');
Route::get('/test-medical-reps', [\App\Modules\MedicalReps\Controllers\MedicalRepsController::class, 'dashboard'])->name('test.medical-reps');
Route::get('/test-compliance', [\App\Modules\Compliance\Controllers\ComplianceController::class, 'dashboard'])->name('test.compliance');
Route::get('/test-whatsapp', [\App\Modules\WhatsApp\Controllers\WhatsAppController::class, 'dashboard'])->name('test.whatsapp');
Route::get('/test-performance', [\App\Modules\Performance\Controllers\PerformanceController::class, 'dashboard'])->name('test.performance');
Route::get('/test-testing', [\App\Modules\Testing\Controllers\TestingController::class, 'dashboard'])->name('test.testing');

// Dashboard route is handled by tenant routes

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
