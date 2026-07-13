<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleAccessRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AdminAuthorizationService;
use App\Services\AdminPermissionCacheService;
use App\Services\AdminPermissionDiscoveryService;
use App\Services\AdminRbacSyncService;
use Illuminate\Http\Request;

class AdminAccessController extends Controller
{
    protected AdminAuthorizationService $authService;

    public function __construct(AdminAuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    public function index()
    {
        $admin = auth()->guard('admin')->user();

        if (!$this->authService->allows($admin, 'access-control', 'can_status')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::withCount('permissions')->orderBy('id')->get();
        $modules = app(AdminPermissionDiscoveryService::class)->discoverModules();

        return view('access-control.index', compact('roles', 'modules'));
    }

    public function create()
    {
        $admin = auth()->guard('admin')->user();

        if (!$this->authService->allows($admin, 'access-control', 'can_create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('access-control.create');
    }

    public function store(StoreRoleRequest $request)
    {
        $admin = auth()->guard('admin')->user();

        if (!$this->authService->allows($admin, 'access-control', 'can_create')) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'is_full_access' => false,
            'is_active' => true,
        ]);

        app(AdminPermissionCacheService::class)->flush($role);

        return redirect()->route('access-control.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $admin = auth()->guard('admin')->user();

        if (!$this->authService->allows($admin, 'access-control', 'can_update')) {
            abort(403, 'Unauthorized action.');
        }

        $modules = app(AdminPermissionDiscoveryService::class)->discoverModules();
        $assignedSlugs = $role->permissions()->pluck('slug')->all();
        $moduleRows = $modules->map(function ($module) use ($assignedSlugs) {
            $moduleKey = $module['module_key'];
            $viewOnly = in_array("{$moduleKey}.can_view", $assignedSlugs, true);
            $viewEdit = $viewOnly
                && in_array("{$moduleKey}.can_create", $assignedSlugs, true)
                && in_array("{$moduleKey}.can_update", $assignedSlugs, true)
                && in_array("{$moduleKey}.can_delete", $assignedSlugs, true)
                && in_array("{$moduleKey}.can_status", $assignedSlugs, true);

            return [
                'module_key' => $moduleKey,
                'module_label' => $module['module_label'],
                'routes' => $module['routes'],
                'access_level' => $viewEdit ? 'view_edit' : ($viewOnly ? 'view_only' : null),
            ];
        });

        return view('access-control.edit', compact('role', 'modules', 'assignedSlugs', 'moduleRows'));
    }

    public function update(UpdateRoleAccessRequest $request, Role $role)
    {
        $admin = auth()->guard('admin')->user();

        if (!$this->authService->allows($admin, 'access-control', 'can_update')) {
            abort(403, 'Unauthorized action.');
        }

        if ($role->is_full_access) {
            return redirect()->back()->with('info', 'Full Access role is managed automatically.');
        }

        $selectedSlugs = [];

        foreach ($request->input('modules', []) as $moduleKey => $accessLevel) {
            $selectedSlugs = array_merge(
                $selectedSlugs,
                app(AdminRbacSyncService::class)->buildModulePermissionSlugs($moduleKey, $accessLevel)
            );
        }

        $permissionIds = Permission::query()
            ->whereIn('slug', $selectedSlugs)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);
        app(AdminPermissionCacheService::class)->flush($role);

        return redirect()->route('access-control.index')->with('success', 'Role permissions updated successfully.');
    }

    public function destroy(Role $role)
    {
        $admin = auth()->guard('admin')->user();

        // Check permission
        if (!$this->authService->allows($admin, 'access-control', 'can_delete')) {
            abort(403, 'Unauthorized action.');
        }

        // Protect system roles (ID 1 and 2)
        if ($role->id <= 2) {
            return redirect()->route('access-control.index')
                ->with('error', 'System roles cannot be deleted.');
        }

        // Check if role is full access
        if ($role->is_full_access) {
            return redirect()->route('access-control.index')
                ->with('error', 'Full Access roles cannot be deleted.');
        }

        // Check if role has admins assigned
        $adminsCount = $role->admins()->count();

        if ($adminsCount > 0) {
            // Optional: Remove role from admins or prevent deletion
            // Option 1: Prevent deletion
            return redirect()->route('access-control.index')
                ->with('error', 'Cannot delete role "' . $role->name . '" because ' . $adminsCount . ' admin(s) are assigned to it. Please reassign them first.');

            // Option 2: Remove role from admins (uncomment if you want this behavior)
            // $role->admins()->update(['role_id' => null]);
        }

        // Store role name for message
        $roleName = $role->name;

        // Delete the role (permissions will be auto-detached due to cascade or manual)
        $role->permissions()->detach(); // Detach all permissions
        $role->delete();

        // Clear cache
        app(AdminPermissionCacheService::class)->flush();

        return redirect()->route('access-control.index')
            ->with('success', 'Role "' . $roleName . '" deleted successfully.');
    }
}
