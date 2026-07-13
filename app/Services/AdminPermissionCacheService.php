<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminPermissionCacheService
{
    public const CACHE_PREFIX = 'admin-rbac';

    public function modules(): Collection
    {
        return Cache::rememberForever($this->key('modules'), function () {
            return app(AdminPermissionDiscoveryService::class)->discoverModules();
        });
    }

    public function permissions(): Collection
    {
        return Cache::rememberForever($this->key('permissions'), function () {
            return Permission::query()->orderBy('module_key')->orderBy('action')->get();
        });
    }

    public function rolePermissionSlugs(Role $role): array
    {
        return Cache::rememberForever($this->key("role.permissions.{$role->id}"), function () use ($role) {
            return $role->permissions()->pluck('slug')->all();
        });
    }

    public function can(Admin $admin, string $moduleKey, string $action = 'can_view'): bool
    {
        Log::debug('AdminPermissionCacheService::can called', [
            'admin_id' => $admin->id,
            'moduleKey' => $moduleKey,
            'action' => $action,
            'is_super_admin' => $admin->isSuperAdmin()
        ]);

        // Super admin can do everything
        if ($admin->isSuperAdmin()) {
            Log::debug('Super admin in AdminPermissionCacheService, returning true');
            return true;
        }

        $role = $admin->assignedRole;

        if (!$role) {
            Log::debug('No role assigned to admin, returning false');
            return false;
        }

        if (!$role->is_active) {
            Log::debug('Role is inactive, returning false');
            return false;
        }

        if ($role->is_full_access) {
            Log::debug('Role has full access, returning true');
            return true;
        }

        $permissionSlugs = $this->rolePermissionSlugs($role);
        $checkingPermission = "{$moduleKey}.{$action}";

        Log::debug('Checking permission', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'checking_permission' => $checkingPermission,
            'available_permissions' => $permissionSlugs,
            'has_permission' => in_array($checkingPermission, $permissionSlugs, true)
        ]);

        return in_array($checkingPermission, $permissionSlugs, true);
    }

    public function flush(?Role $role = null): void
    {
        Cache::forget($this->key('modules'));
        Cache::forget($this->key('permissions'));

        if ($role) {
            Cache::forget($this->key("role.permissions.{$role->id}"));
            return;
        }

        foreach (Role::query()->pluck('id') as $roleId) {
            Cache::forget($this->key("role.permissions.{$roleId}"));
        }
    }

    protected function key(string $suffix): string
    {
        return self::CACHE_PREFIX . '.' . $suffix;
    }
}
