<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;

class AdminAuthorizationService
{
    public function allows(Admin $admin, string $moduleKey, string $action = 'can_view'): bool
    {
        Log::debug('AdminAuthorizationService::allows called', [
            'admin_id' => $admin->id,
            'moduleKey' => $moduleKey,
            'action' => $action,
            'is_super_admin' => $admin->isSuperAdmin()
        ]);

        // Super admin can do everything
        if ($admin->isSuperAdmin()) {
            Log::debug('Super admin in AdminAuthorizationService, returning true');
            return true;
        }

        // Check if role has full access
        if ($admin->assignedRole?->is_full_access) {
            Log::debug('Role has full access in AdminAuthorizationService, returning true');
            return true;
        }

        $result = app(AdminPermissionCacheService::class)->can($admin, $moduleKey, $action);
        Log::debug('AdminAuthorizationService result', ['result' => $result]);

        return $result;
    }

    public function allowsRoute(Admin $admin, string $routeName): bool
    {
        // Super admin can do everything
        if ($admin->isSuperAdmin()) {
            return true;
        }

        // Check if role has full access
        if ($admin->assignedRole?->is_full_access) {
            return true;
        }

        $route = $this->findRouteByName($routeName);

        if (!$route) {
            return false;
        }

        $moduleKey = app(AdminPermissionDiscoveryService::class)->resolveModuleKey($route);
        $action = app(AdminPermissionDiscoveryService::class)->resolveAction($route, $moduleKey);

        return $this->allows($admin, $moduleKey, $action);
    }

    public function currentRoutePermission(): ?array
    {
        $route = request()->route();

        if (!$route instanceof Route) {
            return null;
        }

        $routeName = $route->getName();

        if (!$routeName) {
            return null;
        }

        $moduleKey = app(AdminPermissionDiscoveryService::class)->resolveModuleKey($route);
        $action = app(AdminPermissionDiscoveryService::class)->resolveAction($route, $moduleKey);

        return compact('moduleKey', 'action');
    }

    public function requiredPermissionForCurrentRoute(): ?string
    {
        $permission = $this->currentRoutePermission();

        if (!$permission) {
            return null;
        }

        return $permission['moduleKey'] . '.' . $permission['action'];
    }

    protected function findRouteByName(string $routeName): ?Route
    {
        foreach (RouteFacade::getRoutes() as $route) {
            if ($route->getName() === $routeName) {
                return $route;
            }
        }

        return null;
    }
}
