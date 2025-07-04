<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Modules\Inventory\Controllers\InventoryController;
use App\Modules\Inventory\Controllers\ProductController;
use App\Modules\Sales\Controllers\SalesController;
use App\Modules\Sales\Controllers\POSController;
use App\Modules\Customer\Controllers\CustomerController;
use App\Modules\Supplier\Controllers\SupplierController;
use App\Modules\Purchase\Controllers\PurchaseOrderController;
use App\Http\Controllers\PublicTemplateController;
use App\Modules\Financial\Controllers\CollectionController;
use App\Modules\Financial\Controllers\PaymentPlanController;
use App\Modules\Financial\Controllers\AccountingController;
use App\Modules\Reports\Controllers\ReportsController;
use App\Modules\Reports\Controllers\AnalyticsController;
use App\Modules\AI\Controllers\AIController;
use App\Modules\HR\Controllers\HRController;
use App\Modules\MedicalReps\Controllers\MedicalRepsController;
use App\Modules\Compliance\Controllers\ComplianceController;
use App\Modules\WhatsApp\Controllers\WhatsAppController;
use App\Modules\Performance\Controllers\PerformanceController;
use App\Modules\Testing\Controllers\TestingController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "tenant" middleware group. Now create something great!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return view('tenant.dashboard');
    })->name('tenant.home');

    // Test page for demo
    Route::get('/test', function () {
        return view('test-page');
    })->name('test.page');

    // Templates test page
    Route::get('/templates-test', function () {
        return view('templates-test');
    })->name('templates.test');

    // Export test page
    Route::get('/export-test', function () {
        return view('export-test');
    })->name('export.test');

    // Public template downloads (no auth required)
    Route::get('/templates/suppliers', [PublicTemplateController::class, 'suppliersTemplate'])->name('templates.suppliers');
    Route::get('/templates/customers', [PublicTemplateController::class, 'customersTemplate'])->name('templates.customers');

    // Public exports (no auth required for demo)
    Route::post('/export/suppliers', [PublicTemplateController::class, 'exportSuppliers'])->name('export.suppliers');
    Route::get('/export/suppliers-demo', [PublicTemplateController::class, 'exportSuppliers'])->name('export.suppliers.demo');

    // Authentication routes for tenants
    Route::middleware('guest')->group(function () {
        // Login route moved to web.php for priority
        Route::get('/tenant-login', function () {
            return view('tenant.auth.login');
        })->name('tenant.login');
    });

    Route::middleware(['auth', 'prevent_master_tenant_access'])->group(function () {
        Route::get('/dashboard', fn() => view('tenant.dashboard'))->name('dashboard');
        Route::get('/tenant-dashboard', fn() => view('tenant.dashboard'))->name('tenant.dashboard');

        // Inventory Module Routes
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
            Route::get('/expiring', [InventoryController::class, 'expiring'])->name('expiring');
            Route::get('/movements', [InventoryController::class, 'stockMovements'])->name('movements');
            Route::post('/products/{product}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('products.adjust-stock');

            // Product Management
            Route::prefix('products')->name('products.')->group(function () {
                Route::get('/', [ProductController::class, 'index'])->name('index');
                Route::get('/create', [ProductController::class, 'create'])->name('create');

                // Import/Export routes
                Route::get('/import', [\App\Http\Controllers\ProductImportController::class, 'showImportForm'])->name('import');
                Route::post('/import', [\App\Http\Controllers\ProductImportController::class, 'import'])->name('import.process');
                Route::post('/validate', [\App\Http\Controllers\ProductImportController::class, 'validate'])->name('validate');
                Route::get('/download-template', [\App\Http\Controllers\ProductImportController::class, 'downloadTemplate'])->name('download-template');
                Route::get('/import-progress', [\App\Http\Controllers\ProductImportController::class, 'getProgress'])->name('import.progress');
                Route::post('/', [ProductController::class, 'store'])->name('store');
                Route::get('/{product}', [ProductController::class, 'show'])->name('show');
                Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
                Route::put('/{product}', [ProductController::class, 'update'])->name('update');
                Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
                Route::post('/{product}/duplicate', [ProductController::class, 'duplicate'])->name('duplicate');
                Route::post('/bulk-action', [ProductController::class, 'bulkAction'])->name('bulk-action');
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

        // Sales Module Routes
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/', [SalesController::class, 'index'])->name('index');
            Route::get('/create', [SalesController::class, 'create'])->name('create');
            Route::post('/', [SalesController::class, 'store'])->name('store');
            Route::get('/{sale}', [SalesController::class, 'show'])->name('show');
            Route::get('/{sale}/edit', [SalesController::class, 'edit'])->name('edit');
            Route::put('/{sale}', [SalesController::class, 'update'])->name('update');
            Route::delete('/{sale}', [SalesController::class, 'destroy'])->name('destroy');
            Route::post('/{sale}/add-payment', [SalesController::class, 'addPayment'])->name('add-payment');
            Route::get('/{sale}/print', [SalesController::class, 'printInvoice'])->name('print');
            Route::get('/{sale}/qr-verify', [SalesController::class, 'qrVerification'])->name('qr-verify');
            Route::get('/{sale}/qr-download', [SalesController::class, 'downloadQRCode'])->name('qr-download');
            Route::post('/qr-decode', [SalesController::class, 'decodeQRData'])->name('qr-decode');
            Route::get('/qr-test', function() { return view('tenant.sales.qr-test'); })->name('qr-test');

            // POS Routes
            Route::prefix('pos')->name('pos.')->group(function () {
                Route::get('/', [POSController::class, 'index'])->name('index');
                Route::post('/search-products', [POSController::class, 'searchProducts'])->name('search-products');
                Route::get('/barcode/{barcode}', [POSController::class, 'getProductByBarcode'])->name('barcode');
                Route::post('/process', [POSController::class, 'processSale'])->name('process');
                Route::post('/quick-customer', [POSController::class, 'quickCustomer'])->name('quick-customer');
                Route::post('/hold', [POSController::class, 'holdSale'])->name('hold');
                Route::get('/held-sales', [POSController::class, 'getHeldSales'])->name('held-sales');
                Route::get('/retrieve/{sale}', [POSController::class, 'retrieveHeldSale'])->name('retrieve');
            });
        });

        // Shortcut for POS
        Route::get('/pos', [POSController::class, 'index'])->name('sales.pos');

        // Customers Module Routes
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/import', [CustomerController::class, 'import'])->name('import');
            Route::post('/import', [CustomerController::class, 'processImport'])->name('import.process');
            Route::get('/export', [CustomerController::class, 'export'])->name('export');
            Route::get('/template', [CustomerController::class, 'downloadTemplate'])->name('template');
            Route::post('/{customer}/statement', [CustomerController::class, 'generateStatement'])->name('statement');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
            Route::get('/{customer}/statements', [CustomerController::class, 'statements'])->name('statements');
            Route::post('/{customer}/adjust-credit', [CustomerController::class, 'adjustCredit'])->name('adjust-credit');
            Route::get('/{customer}/loyalty-history', [CustomerController::class, 'loyaltyHistory'])->name('loyalty-history');
            Route::post('/{customer}/adjust-loyalty', [CustomerController::class, 'adjustLoyaltyPoints'])->name('adjust-loyalty');
            Route::post('/bulk-action', [CustomerController::class, 'bulkAction'])->name('bulk-action');
        });

        // Suppliers Module Routes
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
            Route::post('/{supplier}/evaluate', [SupplierController::class, 'evaluate'])->name('evaluate');
            Route::get('/{supplier}/performance', [SupplierController::class, 'performance'])->name('performance');
            Route::get('/{supplier}/products', [SupplierController::class, 'products'])->name('products');
            Route::post('/{supplier}/add-product', [SupplierController::class, 'addProduct'])->name('add-product');
            Route::delete('/{supplier}/remove-product/{product}', [SupplierController::class, 'removeProduct'])->name('remove-product');
            Route::post('/bulk-action', [SupplierController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/import', [SupplierController::class, 'showImport'])->name('import');
            Route::post('/import', [SupplierController::class, 'import'])->name('import.process');
            Route::get('/export', [SupplierController::class, 'export'])->name('export');
            Route::get('/template', [SupplierController::class, 'downloadTemplate'])->name('template');
        });

        // Purchase Orders routes
        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
            Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('show');
            Route::get('/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
            Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('update');
            Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
            Route::post('/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('approve');
            Route::post('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('receive');
            Route::get('/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('print');
        });

        // Financial Module Routes
        Route::prefix('financial')->name('financial.')->group(function () {
            Route::get('/', fn() => view('tenant.financial.index'))->name('index');

            // Collections Routes
            Route::prefix('collections')->name('collections.')->group(function () {
                Route::get('/', [CollectionController::class, 'index'])->name('index');
                Route::get('/dashboard', [CollectionController::class, 'dashboard'])->name('dashboard');
                Route::get('/create', [CollectionController::class, 'create'])->name('create');
                Route::post('/', [CollectionController::class, 'store'])->name('store');
                Route::get('/{collection}', [CollectionController::class, 'show'])->name('show');
                Route::get('/{collection}/edit', [CollectionController::class, 'edit'])->name('edit');
                Route::put('/{collection}', [CollectionController::class, 'update'])->name('update');
                Route::post('/{collection}/add-payment', [CollectionController::class, 'addPayment'])->name('add-payment');
                Route::post('/{collection}/add-activity', [CollectionController::class, 'addActivity'])->name('add-activity');
                Route::post('/{collection}/mark-contacted', [CollectionController::class, 'markAsContacted'])->name('mark-contacted');
                Route::post('/{collection}/apply-discount', [CollectionController::class, 'applyDiscount'])->name('apply-discount');
                Route::post('/{collection}/write-off', [CollectionController::class, 'writeOff'])->name('write-off');
                Route::post('/bulk-action', [CollectionController::class, 'bulkAction'])->name('bulk-action');
            });

            // Payment Plans Routes
            Route::prefix('payment-plans')->name('payment-plans.')->group(function () {
                Route::get('/', [PaymentPlanController::class, 'index'])->name('index');
                Route::get('/create', [PaymentPlanController::class, 'create'])->name('create');
                Route::post('/', [PaymentPlanController::class, 'store'])->name('store');
                Route::get('/{paymentPlan}', [PaymentPlanController::class, 'show'])->name('show');
                Route::get('/{paymentPlan}/edit', [PaymentPlanController::class, 'edit'])->name('edit');
                Route::put('/{paymentPlan}', [PaymentPlanController::class, 'update'])->name('update');
                Route::delete('/{paymentPlan}', [PaymentPlanController::class, 'destroy'])->name('destroy');
                Route::post('/{paymentPlan}/activate', [PaymentPlanController::class, 'activate'])->name('activate');
                Route::post('/{paymentPlan}/suspend', [PaymentPlanController::class, 'suspend'])->name('suspend');
            });

            // Accounting Routes
            Route::prefix('accounting')->name('accounting.')->group(function () {
                Route::get('/', [AccountingController::class, 'dashboard'])->name('dashboard');
                Route::get('/chart-of-accounts', [AccountingController::class, 'chartOfAccounts'])->name('chart-of-accounts');
                Route::get('/trial-balance', [AccountingController::class, 'trialBalance'])->name('trial-balance');
                Route::get('/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('balance-sheet');
                Route::get('/income-statement', [AccountingController::class, 'incomeStatement'])->name('income-statement');
                Route::get('/general-ledger', [AccountingController::class, 'generalLedger'])->name('general-ledger');
                Route::get('/journal-entries', [AccountingController::class, 'journalEntries'])->name('journal-entries');
            });
        });

        // Reports & Analytics Module Routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportsController::class, 'index'])->name('index');
            Route::get('/dashboard', [ReportsController::class, 'dashboard'])->name('dashboard');
            Route::get('/create', [ReportsController::class, 'create'])->name('create');
            Route::post('/', [ReportsController::class, 'store'])->name('store');

            // Specific Reports
            Route::get('/sales', [ReportsController::class, 'salesReport'])->name('sales');
            Route::get('/inventory', [ReportsController::class, 'inventoryReport'])->name('inventory');
            Route::get('/financial', [ReportsController::class, 'financialReport'])->name('financial');
            Route::get('/customers', [ReportsController::class, 'customersReport'])->name('customers');
            Route::post('/customers/export', [ReportsController::class, 'exportCustomersReport'])->name('customers.export');
            Route::get('/suppliers', [ReportsController::class, 'suppliersReport'])->name('suppliers');
            Route::post('/suppliers/export', [ReportsController::class, 'exportSuppliersReport'])->name('suppliers.export');
            Route::get('/products', [ReportsController::class, 'productsReport'])->name('products');
            Route::get('/purchases', [ReportsController::class, 'purchasesReport'])->name('purchases');
            Route::get('/profit-loss', [ReportsController::class, 'profitLossReport'])->name('profit-loss');
            Route::get('/balance-sheet', [ReportsController::class, 'balanceSheetReport'])->name('balance-sheet');
            Route::get('/cash-flow', [ReportsController::class, 'cashFlowReport'])->name('cash-flow');

            // Generic report routes
            Route::get('/{report}', [ReportsController::class, 'show'])->name('show');
            Route::post('/{report}/run', [ReportsController::class, 'run'])->name('run');
            Route::post('/{report}/export', [ReportsController::class, 'export'])->name('export');
        });

        // Analytics Routes
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'dashboard'])->name('dashboard');
            Route::get('/sales', [AnalyticsController::class, 'salesAnalytics'])->name('sales');
            Route::get('/customers', [AnalyticsController::class, 'customerAnalytics'])->name('customers');
            Route::get('/products', [AnalyticsController::class, 'productAnalytics'])->name('products');
            Route::get('/profitability', [AnalyticsController::class, 'profitabilityAnalysis'])->name('profitability');
        });

        // AI & Prediction Tools Routes
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/', [AIController::class, 'dashboard'])->name('dashboard');
            Route::get('/demand-forecasting', [AIController::class, 'demandForecasting'])->name('demand-forecasting');
            Route::post('/demand-forecasting', [AIController::class, 'createDemandForecast'])->name('create-demand-forecast');
            Route::get('/price-optimization', [AIController::class, 'priceOptimization'])->name('price-optimization');
            Route::post('/price-optimization', [AIController::class, 'createPriceOptimization'])->name('create-price-optimization');
            Route::get('/customer-analytics', [AIController::class, 'customerAnalytics'])->name('customer-analytics');
            Route::post('/customer-behavior', [AIController::class, 'analyzeCustomerBehavior'])->name('analyze-customer-behavior');
            Route::post('/churn-prediction', [AIController::class, 'predictChurnRisk'])->name('predict-churn-risk');
            Route::post('/batch-analysis', [AIController::class, 'batchAnalysis'])->name('batch-analysis');
            Route::get('/predictions/{prediction}', [AIController::class, 'predictionDetails'])->name('prediction-details');
            Route::post('/predictions/{prediction}/accuracy', [AIController::class, 'updatePredictionAccuracy'])->name('update-accuracy');
            Route::get('/settings', [AIController::class, 'aiSettings'])->name('settings');
            Route::post('/settings', [AIController::class, 'updateSettings'])->name('update-settings');
        });

        // Human Resources Module Routes
        Route::prefix('hr')->name('hr.')->group(function () {
            Route::get('/', [HRController::class, 'dashboard'])->name('dashboard');

            // Employee Management
            Route::prefix('employees')->name('employees.')->group(function () {
                Route::get('/', [HRController::class, 'employees'])->name('index');
                Route::get('/create', [HRController::class, 'createEmployee'])->name('create');
                Route::post('/', [HRController::class, 'storeEmployee'])->name('store');
                Route::get('/{employee}', [HRController::class, 'showEmployee'])->name('show');
                Route::get('/{employee}/edit', [HRController::class, 'editEmployee'])->name('edit');
                Route::put('/{employee}', [HRController::class, 'updateEmployee'])->name('update');
                Route::delete('/{employee}', [HRController::class, 'destroyEmployee'])->name('destroy');
            });

            // Attendance Management
            Route::prefix('attendance')->name('attendance.')->group(function () {
                Route::get('/', [HRController::class, 'attendance'])->name('index');
                Route::post('/mark', [HRController::class, 'markAttendance'])->name('mark');
                Route::get('/report', [HRController::class, 'attendanceReport'])->name('report');
            });

            // Leave Management
            Route::prefix('leave')->name('leave.')->group(function () {
                Route::get('/', [HRController::class, 'leaveRequests'])->name('index');
                Route::get('/create', [HRController::class, 'createLeaveRequest'])->name('create');
                Route::post('/', [HRController::class, 'storeLeaveRequest'])->name('store');
                Route::post('/{leaveRequest}/approve', [HRController::class, 'approveLeaveRequest'])->name('approve');
            });

            // Department Management
            Route::prefix('departments')->name('departments.')->group(function () {
                Route::get('/', [HRController::class, 'departments'])->name('index');
            });
        });

        // Medical Sales Representatives Module Routes
        Route::prefix('medical-reps')->name('medical-reps.')->group(function () {
            Route::get('/', [MedicalRepsController::class, 'dashboard'])->name('dashboard');

            // Representatives Management
            Route::prefix('reps')->name('reps.')->group(function () {
                Route::get('/', [MedicalRepsController::class, 'reps'])->name('index');
                Route::get('/create', [MedicalRepsController::class, 'createRep'])->name('create');
                Route::post('/', [MedicalRepsController::class, 'storeRep'])->name('store');
                Route::get('/{rep}', [MedicalRepsController::class, 'showRep'])->name('show');
                Route::get('/{rep}/edit', [MedicalRepsController::class, 'editRep'])->name('edit');
                Route::put('/{rep}', [MedicalRepsController::class, 'updateRep'])->name('update');
            });

            // Visit Management
            Route::prefix('visits')->name('visits.')->group(function () {
                Route::get('/', [MedicalRepsController::class, 'visits'])->name('index');
                Route::get('/create', [MedicalRepsController::class, 'createVisit'])->name('create');
                Route::post('/', [MedicalRepsController::class, 'storeVisit'])->name('store');
                Route::post('/{visit}/check-in', [MedicalRepsController::class, 'checkInVisit'])->name('check-in');
                Route::post('/{visit}/check-out', [MedicalRepsController::class, 'checkOutVisit'])->name('check-out');
            });

            // Territory Management
            Route::prefix('territories')->name('territories.')->group(function () {
                Route::get('/', [MedicalRepsController::class, 'territories'])->name('index');
                Route::get('/{territory}', [MedicalRepsController::class, 'showTerritory'])->name('show');
            });

            // Performance & Analytics
            Route::prefix('performance')->name('performance.')->group(function () {
                Route::get('/', [MedicalRepsController::class, 'performance'])->name('index');
                Route::get('/rep/{rep}', [MedicalRepsController::class, 'repPerformance'])->name('rep');
            });

            // Mobile App Interface
            Route::get('/mobile', [MedicalRepsController::class, 'mobileApp'])->name('mobile');
        });

        // Regulatory Compliance Module Routes
        Route::prefix('compliance')->name('compliance.')->group(function () {
            Route::get('/', [ComplianceController::class, 'dashboard'])->name('dashboard');

            // Compliance Items Management
            Route::prefix('items')->name('items.')->group(function () {
                Route::get('/', [ComplianceController::class, 'items'])->name('index');
                Route::get('/create', [ComplianceController::class, 'createItem'])->name('create');
                Route::post('/', [ComplianceController::class, 'storeItem'])->name('store');
                Route::get('/{item}', [ComplianceController::class, 'showItem'])->name('show');
                Route::get('/{item}/edit', [ComplianceController::class, 'editItem'])->name('edit');
                Route::put('/{item}', [ComplianceController::class, 'updateItem'])->name('update');
                Route::post('/{item}/renew', [ComplianceController::class, 'renewItem'])->name('renew');
            });

            // Inspection Management
            Route::prefix('inspections')->name('inspections.')->group(function () {
                Route::get('/', [ComplianceController::class, 'inspections'])->name('index');
                Route::get('/create', [ComplianceController::class, 'createInspection'])->name('create');
                Route::post('/', [ComplianceController::class, 'storeInspection'])->name('store');
                Route::get('/{inspection}', [ComplianceController::class, 'showInspection'])->name('show');
                Route::post('/{inspection}/complete', [ComplianceController::class, 'completeInspection'])->name('complete');
            });

            // Violation Management
            Route::prefix('violations')->name('violations.')->group(function () {
                Route::get('/', [ComplianceController::class, 'violations'])->name('index');
                Route::get('/create', [ComplianceController::class, 'createViolation'])->name('create');
                Route::post('/', [ComplianceController::class, 'storeViolation'])->name('store');
                Route::get('/{violation}', [ComplianceController::class, 'showViolation'])->name('show');
                Route::post('/{violation}/resolve', [ComplianceController::class, 'resolveViolation'])->name('resolve');
            });

            // Compliance Reports
            Route::get('/reports', [ComplianceController::class, 'reports'])->name('reports');
        });

        // WhatsApp Integration Module Routes
        Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
            Route::get('/', [WhatsAppController::class, 'dashboard'])->name('dashboard');

            // Message Management
            Route::prefix('messages')->name('messages.')->group(function () {
                Route::get('/', [WhatsAppController::class, 'messages'])->name('index');
                Route::get('/create', [WhatsAppController::class, 'createMessage'])->name('create');
                Route::post('/', [WhatsAppController::class, 'storeMessage'])->name('store');
                Route::get('/{message}', [WhatsAppController::class, 'showMessage'])->name('show');
                Route::post('/{message}/resend', [WhatsAppController::class, 'resendMessage'])->name('resend');
            });

            // Template Management
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [WhatsAppController::class, 'templates'])->name('index');
                Route::get('/create', [WhatsAppController::class, 'createTemplate'])->name('create');
                Route::post('/', [WhatsAppController::class, 'storeTemplate'])->name('store');
                Route::get('/{template}', [WhatsAppController::class, 'showTemplate'])->name('show');
            });

            // Business Actions
            Route::post('/send-invoice/{sale}', [WhatsAppController::class, 'sendInvoice'])->name('send.invoice');
            Route::post('/send-payment-reminder/{sale}', [WhatsAppController::class, 'sendPaymentReminder'])->name('send.payment_reminder');
            Route::post('/bulk-send', [WhatsAppController::class, 'bulkSend'])->name('bulk.send');

            // System Actions
            Route::post('/process-queue', [WhatsAppController::class, 'processQueue'])->name('process.queue');
            Route::get('/settings', [WhatsAppController::class, 'settings'])->name('settings');
        });

        // WhatsApp Webhook (outside auth middleware)
        Route::post('/webhook/whatsapp', [WhatsAppController::class, 'webhook'])->name('whatsapp.webhook');

        // Performance Optimization Module Routes
        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/', [PerformanceController::class, 'dashboard'])->name('dashboard');

            // Cache Management
            Route::get('/cache', [PerformanceController::class, 'cacheManagement'])->name('cache');
            Route::post('/cache/clear', [PerformanceController::class, 'clearCache'])->name('cache.clear');
            Route::post('/cache/warmup', [PerformanceController::class, 'warmUpCache'])->name('cache.warmup');

            // Database Optimization
            Route::get('/database', [PerformanceController::class, 'databaseOptimization'])->name('database');
            Route::post('/database/optimize', [PerformanceController::class, 'optimizeDatabase'])->name('database.optimize');

            // Performance Monitoring
            Route::get('/monitoring', [PerformanceController::class, 'performanceMonitoring'])->name('monitoring');
            Route::get('/metrics', [PerformanceController::class, 'getMetrics'])->name('metrics');
            Route::get('/alerts', [PerformanceController::class, 'getAlerts'])->name('alerts');

            // Reports and Optimization
            Route::post('/report', [PerformanceController::class, 'generateReport'])->name('report');
            Route::post('/optimize', [PerformanceController::class, 'optimizePerformance'])->name('optimize');

            // Redis Monitoring
            Route::get('/redis', [PerformanceController::class, 'redisMonitoring'])->name('redis.monitoring');
            Route::post('/redis/clear', [PerformanceController::class, 'clearRedisCache'])->name('redis.clear');
            Route::post('/redis/warmup', [PerformanceController::class, 'warmUpRedisCache'])->name('redis.warmup');
        });

        // Testing & Quality Assurance Module Routes
        Route::prefix('testing')->name('testing.')->group(function () {
            Route::get('/', [TestingController::class, 'dashboard'])->name('dashboard');

            // Test Execution
            Route::post('/run', [TestingController::class, 'runTests'])->name('run');
            Route::post('/coverage', [TestingController::class, 'generateCoverage'])->name('coverage');
            Route::post('/quality', [TestingController::class, 'runQualityChecks'])->name('quality');

            // Test Results and Reports
            Route::get('/results', [TestingController::class, 'getTestResults'])->name('results');
            Route::get('/coverage-report', [TestingController::class, 'getCoverageReport'])->name('coverage.report');
            Route::get('/quality-report', [TestingController::class, 'getQualityReport'])->name('quality.report');

            // Module Testing
            Route::get('/modules', [TestingController::class, 'testModules'])->name('modules');
            Route::post('/modules/{module}', [TestingController::class, 'runModuleTest'])->name('modules.test');
        });
    });
});
