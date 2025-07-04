<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\TenantController;
use App\Modules\Inventory\Controllers\InventoryController;

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
