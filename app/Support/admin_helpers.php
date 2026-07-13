<?php

use App\Models\Admin;
use App\Services\AdminAuthorizationService;
use Illuminate\Support\Facades\Auth;

if (! function_exists('admin_user')) {
    function admin_user(): ?Admin
    {
        return Auth::guard('admin')->user();
    }
}

if (! function_exists('admin_is_super_admin')) {
    function admin_is_super_admin(): bool
    {
        return admin_user()?->isSuperAdmin() ?? false;
    }
}

if (! function_exists('admin_can')) {
    function admin_can(string $moduleKey, string $action = 'can_view'): bool
    {
        $admin = admin_user();

        if (! $admin) {
            return false;
        }

        return app(AdminAuthorizationService::class)->allows($admin, $moduleKey, $action);
    }
}

if (! function_exists('admin_can_route')) {
    function admin_can_route(string $routeName): bool
    {
        $admin = admin_user();

        if (! $admin) {
            return false;
        }

        return app(AdminAuthorizationService::class)->allowsRoute($admin, $routeName);
    }
}
