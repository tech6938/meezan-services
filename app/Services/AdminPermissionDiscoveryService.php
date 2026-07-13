<?php

namespace App\Services;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;

class AdminPermissionDiscoveryService
{
    public const ACTIONS = [
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
        'can_status',
        'can_export',
        'can_import',
        'can_restore',
        'can_approve',
        'can_print',
    ];

    public function discoverModules(): Collection
    {
        return $this->discoverRoutes()
            ->groupBy('module_key')
            ->map(function (Collection $routes, string $moduleKey) {
                return [
                    'module_key' => $moduleKey,
                    'module_label' => $routes->first()['module_label'],
                    'routes' => $routes->values()->all(),
                ];
            })
            ->values();
    }

    public function discoverPermissions(): Collection
    {
        return $this->discoverRoutes()
            ->unique('slug')
            ->values();
    }

    public function moduleKeys(): array
    {
        return $this->discoverModules()->pluck('module_key')->all();
    }

    protected function discoverRoutes(): Collection
    {
        return collect(RouteFacade::getRoutes())
            ->map(fn (Route $route) => $this->mapRoute($route))
            ->filter()
            ->values();
    }

    protected function mapRoute(Route $route): ?array
    {
        $name = $route->getName();
        $actionName = $route->getActionName();
        $uri = trim($route->uri(), '/');
        $methods = array_values(array_diff($route->methods(), ['HEAD', 'OPTIONS']));

        if (!$name || $this->isIgnoredRoute($name, $uri)) {
            return null;
        }

        if (Str::startsWith($actionName, 'Closure')) {
            return null;
        }

        $moduleKey = $this->resolveModuleKey($route);
        $moduleLabel = Str::headline(str_replace(['_', '.'], ' ', $moduleKey));
        $action = $this->resolveAction($route, $moduleKey);

        return [
            'module_key' => $moduleKey,
            'module_label' => $moduleLabel,
            'action' => $action,
            'slug' => "{$moduleKey}.{$action}",
            'route_name' => $name,
            'uri' => $uri,
            'http_methods' => $methods,
            'description' => $this->buildDescription($moduleLabel, $action),
        ];
    }

    public function resolveModuleKey(Route $route): string
    {
        $name = (string) $route->getName();
        $uri = trim($route->uri(), '/');
        $actionName = $route->getActionName();
        $controller = Str::before($actionName, '@');
        $method = Str::after($actionName, '@');
        $controllerBase = class_basename($controller);

        if ($name === 'dashboard' || Str::contains($uri, 'dashboard')) {
            return 'dashboard';
        }

        if ($controllerBase === 'UserProviderController') {
            return Str::startsWith($method, 'provider') ? 'providers' : 'users';
        }

        if (in_array($controllerBase, ['MainCategoryController', 'SubCategoryController'], true)) {
            return Str::before($name, '.');
        }

        if (in_array($controllerBase, [
            'ChatsController',
            'ServiceRequestController',
            'BookingController',
            'ShopController',
            'SettingController',
            'ReferralController',
            'PageController',
            'CommissionController',
            'TaxController',
            'VolunteerController',
            'AdminAccessController',
            'AdminController',
        ], true)) {
            return match ($controllerBase) {
                'SettingController' => 'settings',
                'ReferralController' => 'referrals',
                'ServiceRequestController' => 'requests',
                'BookingController' => 'bookings',
                'ChatsController' => 'chats',
                'ShopController' => 'shops',
                'CommissionController' => 'commission',
                'TaxController' => 'tax',
                'PageController' => 'pages',
                'VolunteerController' => 'volunteers',
                'AdminAccessController' => 'access-control',
                'AdminController' => 'admin-management',
                default => Str::slug(Str::before($controllerBase, 'Controller')),
            };
        }

        if (Str::contains($name, 'main-categories')) {
            return 'main-categories';
        }

        if (Str::contains($name, 'sub-categories')) {
            return 'sub-categories';
        }

        if (Str::contains($name, 'appUrl') || Str::contains($name, 'settings.') || Str::contains($uri, 'privacyPolicy') || Str::contains($uri, 'terms&conditions')) {
            return 'settings';
        }

        if (Str::contains($name, 'referrals.')) {
            return 'referrals';
        }

        if (Str::contains($name, 'commission.')) {
            return 'commission';
        }

        if (Str::contains($name, 'pages.')) {
            return 'pages';
        }

        if (Str::contains($uri, 'admin/chat')) {
            return 'chats';
        }

        $firstSegment = Str::of($uri)->before('/')->toString();
        $fallback = $firstSegment ?: Str::before($name, '.');

        return Str::slug($fallback);
    }

    public function resolveAction(Route $route, string $moduleKey): string
    {
        $name = (string) $route->getName();
        $uri = trim($route->uri(), '/');
        $method = collect($route->methods())->first(fn ($value) => !in_array($value, ['HEAD', 'OPTIONS'], true), 'GET');

        $lowerName = Str::lower($name);
        $lowerUri = Str::lower($uri);

        if (Str::contains($lowerName, ['export'])) {
            return 'can_export';
        }

        if (Str::contains($lowerName, ['import'])) {
            return 'can_import';
        }

        if (Str::contains($lowerName, ['restore'])) {
            return 'can_restore';
        }

        if (Str::contains($lowerName, ['approve'])) {
            return 'can_approve';
        }

        if (Str::contains($lowerName, ['print'])) {
            return 'can_print';
        }

        if (Str::contains($lowerName, ['delete', 'destroy', 'force-delete'])) {
            return 'can_delete';
        }

        if (Str::contains($lowerName, ['status', 'toggle', 'activate', 'deactivate', 'on', 'off'])) {
            return 'can_status';
        }

        if ($method === 'DELETE') {
            return 'can_delete';
        }

        if (in_array($method, ['PUT', 'PATCH'], true) || Str::contains($lowerName, ['update'])) {
            return 'can_update';
        }

        if ($method === 'POST' && (Str::contains($lowerName, ['store', 'create', 'save']) || Str::contains($lowerUri, ['store']))) {
            return 'can_create';
        }

        if (Str::contains($lowerName, ['create'])) {
            return 'can_create';
        }

        if (Str::contains($lowerName, ['edit'])) {
            return 'can_update';
        }

        return 'can_view';
    }

    protected function buildDescription(string $moduleLabel, string $action): string
    {
        return "{$moduleLabel} - {$action}";
    }

    protected function isIgnoredRoute(string $name, string $uri): bool
    {
        $ignoredPrefixes = [
            'login', 'match-login', 'signup', 'insert-signup', 'forget', 'forget_message',
            'otp', 'matching_route', 'reset', 'reset_password', 'logout',
            'api.', 'sanctum.', 'ignition.', 'debugbar.', 'horizon.',
        ];

        foreach ($ignoredPrefixes as $prefix) {
            if (Str::startsWith($name, $prefix)) {
                return true;
            }
        }

        return Str::contains($uri, ['api/']);
    }
}
