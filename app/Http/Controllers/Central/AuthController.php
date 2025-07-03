<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the master admin login form
     */
    public function showLoginForm()
    {
        return view('central.auth.login');
    }

    /**
     * Handle master admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user and check if they are super admin
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials are incorrect.'),
            ]);
        }

        if (!$user->is_super_admin) {
            throw ValidationException::withMessages([
                'email' => __('Access denied. Super admin privileges required.'),
            ]);
        }

        // Login the user with guard specification
        Auth::guard('web')->login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Force redirect to master admin dashboard
        return redirect()->route('central.dashboard')->with('success', __('Welcome back, Master Admin!'));
    }

    /**
     * Handle master admin logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('central.login');
    }
}
