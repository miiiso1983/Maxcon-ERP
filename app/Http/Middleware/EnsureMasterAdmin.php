<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMasterAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('central.login')->with('error', __('Please login to access the master admin panel.'));
        }

        // Check if user is super admin
        if (!Auth::user()->is_super_admin) {
            Auth::logout();
            return redirect()->route('central.login')->with('error', __('Access denied. Super admin privileges required.'));
        }

        // Prevent access to tenant routes
        if ($request->is('dashboard') || $request->is('tenant-dashboard') || $request->is('tenant/*')) {
            return redirect()->route('central.dashboard');
        }

        return $next($request);
    }
}
