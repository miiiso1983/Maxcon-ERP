<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::with('tenant')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when(request('tenant_id'), function ($query, $tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(request('role'), function ($query, $role) {
                if ($role === 'super_admin') {
                    $query->where('is_super_admin', true);
                } else {
                    $query->where('is_super_admin', false);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $tenants = Tenant::where('status', 'active')->get();

        return view('central.users.index', compact('users', 'tenants'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $tenants = Tenant::where('status', 'active')->get();
        return view('central.users.create', compact('tenants'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'tenant_id' => 'nullable|exists:tenants,id',
            'is_super_admin' => 'boolean',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $request->tenant_id,
            'is_super_admin' => $request->boolean('is_super_admin'),
            'status' => $request->status,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('central.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $tenants = Tenant::where('status', 'active')->get();
        return view('central.users.edit', compact('user', 'tenants'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'tenant_id' => 'nullable|exists:tenants,id',
            'is_super_admin' => 'boolean',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'tenant_id' => $request->tenant_id,
            'is_super_admin' => $request->boolean('is_super_admin'),
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('central.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deletion of super admin users if they're the only one
        if ($user->is_super_admin) {
            $superAdminCount = User::where('is_super_admin', true)->count();
            if ($superAdminCount <= 1) {
                return redirect()->route('central.users.index')
                    ->with('error', 'Cannot delete the last super admin user.');
            }
        }

        // Prevent deletion of tenant admin users
        if ($user->tenant_id && Tenant::where('admin_user_id', $user->id)->exists()) {
            return redirect()->route('central.users.index')
                ->with('error', 'Cannot delete a tenant admin user. Please assign a new admin first.');
        }

        $user->delete();

        return redirect()->route('central.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
