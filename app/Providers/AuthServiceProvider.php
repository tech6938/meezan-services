<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use App\Policies\AdminPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Services\AdminPermissionCacheService;
use App\Services\AdminRbacSyncService;
use App\Services\AdminAuthorizationService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Admin::class => AdminPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        $hasRbacTables = Schema::hasTable('roles') && Schema::hasTable('permissions');

        if ($hasRbacTables) {
            app(AdminRbacSyncService::class)->sync();
        }

        // Only handle super admin in Gate::before
        Gate::before(function ($user, string $ability) {
            if (!$user instanceof Admin) {
                return null;
            }

            if ($user->isSuperAdmin()) {
                return true;
            }

            if ($user->assignedRole?->is_full_access) {
                return true;
            }

            return null;
        });

        // Blade directives using your Authorization Service directly
        Blade::if('canManageAdmins', function () {
            $admin = auth()->guard('admin')->user();
            if (!$admin) return false;

            return app(AdminAuthorizationService::class)->allows($admin, 'admin-management', 'can_view');
        });

        Blade::if('canManageAccessControl', function () {
            $admin = auth()->guard('admin')->user();
            if (!$admin) return false;

            return app(AdminAuthorizationService::class)->allows($admin, 'access-control', 'can_status');
        });

        // Keep existing Blade directives
        Blade::if('adminCan', function (string $moduleKey, string $action = 'can_view') {
            return admin_can($moduleKey, $action);
        });

        Blade::if('adminRouteAllowed', function (string $routeName) {
            return admin_can_route($routeName);
        });
    }
}
