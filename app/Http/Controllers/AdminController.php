<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Role;
use App\Services\AdminAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    protected AdminAuthorizationService $authService;

    public function __construct(AdminAuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    public function index()
    {
        $admin = auth()->guard('admin')->user();

        if (!$this->authService->allows($admin, 'admin-management', 'can_view')) {
            abort(403, 'Unauthorized action.');
        }

        $admins = Admin::with('assignedRole')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.index', compact('admins'));
    }

    public function create()
    {
        $admin = auth()->guard('admin')->user();

        if (!$this->authService->allows($admin, 'admin-management', 'can_create')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::where('is_active', true)->get();
        return view('admin.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        if (!$this->authService->allows($admin, 'admin-management', 'can_create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_super_admin' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_super_admin'] = $request->has('is_super_admin') ? 1 : 0;

        Admin::create($validated);

        return redirect()->route('admin.index')->with('success', 'Admin created successfully.');
    }

    public function edit(Admin $admin)
    {
        $currentAdmin = auth()->guard('admin')->user();

        if (!$this->authService->allows($currentAdmin, 'admin-management', 'can_update')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent editing yourself
        if ($currentAdmin->id === $admin->id) {
            return redirect()->route('admin.index')->with('error', 'You cannot edit your own account.');
        }

        $roles = Role::where('is_active', true)->get();
        return view('admin.edit', compact('admin', 'roles', 'currentAdmin'));
    }

    public function update(Request $request, Admin $admin)
    {
        $currentAdmin = auth()->guard('admin')->user();

        if (!$this->authService->allows($currentAdmin, 'admin-management', 'can_update')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent updating yourself
        if ($currentAdmin->id === $admin->id) {
            return redirect()->route('admin.index')->with('error', 'You cannot update your own account.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_super_admin' => 'boolean',
        ]);

        // Only super admins can assign super admin status
        if (!$currentAdmin->isSuperAdmin()) {
            if (!$admin->is_super_admin) {
                $validated['is_super_admin'] = 0;
            } else {
                $validated['is_super_admin'] = $admin->is_super_admin;
            }
        } else {
            $validated['is_super_admin'] = $request->has('is_super_admin') ? 1 : 0;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $admin->update($validated);

        return redirect()->route('admin.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(Admin $admin)
    {
        $currentAdmin = auth()->guard('admin')->user();

        if (!$this->authService->allows($currentAdmin, 'admin-management', 'can_delete')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent deleting yourself
        if ($currentAdmin->id === $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ], 400);
        }

        // Prevent deleting the last super admin
        if ($admin->is_super_admin && Admin::where('is_super_admin', 1)->count() === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the last super admin.'
            ], 400);
        }

        $admin->delete();
        return redirect()->route('admin.index')->with('success', 'Admin deleted successfully.');
    }
}
