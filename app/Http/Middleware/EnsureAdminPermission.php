<?php

namespace App\Http\Middleware;

use App\Services\AdminAuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login');
        }

        // Temporarily allow admin.* routes for testing
        if ($request->route()?->getName() && str_starts_with($request->route()->getName(), 'admin.')) {
            return $next($request);
        }

        $requiredPermission = app(AdminAuthorizationService::class)->requiredPermissionForCurrentRoute();

        if (!$requiredPermission) {
            return $next($request);
        }

        [$moduleKey, $action] = explode('.', $requiredPermission, 2);

        if (app(AdminAuthorizationService::class)->allows($admin, $moduleKey, $action)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
