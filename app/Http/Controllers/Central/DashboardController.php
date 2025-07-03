<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'expired_licenses' => Tenant::where('license_expires_at', '<', now())->count(),
            'total_users' => User::count(),
        ];

        $recent_tenants = Tenant::latest()->take(5)->get();
        $expiring_licenses = Tenant::where('license_expires_at', '<=', now()->addDays(30))
            ->where('license_expires_at', '>', now())
            ->get();

        return view('central.dashboard-new', compact('stats', 'recent_tenants', 'expiring_licenses'));
    }
}
