<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventMasterAdminTenantAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated and is super admin, redirect to master admin dashboard
        if (Auth::check() && Auth::user()->is_super_admin) {
            return redirect()->route('central.dashboard')->with('info', __('You are logged in as Master Admin. Use the master admin panel.'));
        }

        return $next($request);
    }
}
