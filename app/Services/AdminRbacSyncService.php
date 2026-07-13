<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminRbacSyncService
{
    public function sync(): array
    {
        $discovery = app(AdminPermissionDiscoveryService::class);
        $cache = app(AdminPermissionCacheService::class);

        $permissions = $discovery->discoverPermissions();
        $hash = md5(json_encode($permissions->pluck('slug')->sort()->values()->all()));
        $currentHash = Cache::get('admin-rbac.discovery.hash');

        if ($currentHash === $hash && Permission::query()->exists() && Role::query()->exists()) {
            return [
                'permissions' => Permission::count(),
                'roles' => Role::count(),
            ];
        }

        DB::transaction(function () use ($permissions, $cache) {
            foreach ($permissions as $permissionData) {
                Permission::updateOrCreate(
                    ['slug' => $permissionData['slug']],
                    [
                        'module_key' => $permissionData['module_key'],
                        'module_label' => $permissionData['module_label'],
                        'action' => $permissionData['action'],
                        'route_name' => $permissionData['route_name'],
                        'uri' => $permissionData['uri'],
                        'http_methods' => $permissionData['http_methods'],
                        'description' => $permissionData['description'],
                        'is_active' => true,
                    ]
                );
            }

            $fullAccessRole = Role::updateOrCreate(
                ['slug' => 'full-access'],
                [
                    'name' => 'Full Access',
                    'description' => 'Access to every module and action.',
                    'is_full_access' => true,
                    'is_active' => true,
                ]
            );

            $partialAccessRole = Role::updateOrCreate(
                ['slug' => 'partial-access'],
                [
                    'name' => 'Partial Access',
                    'description' => 'Module based access with view-only or view+edit options.',
                    'is_full_access' => false,
                    'is_active' => true,
                ]
            );

            $allPermissionIds = Permission::query()->pluck('id')->all();
            $viewOnlyIds = Permission::query()
                ->where('action', 'can_view')
                ->pluck('id')
                ->all();
            $fullAccessRole->permissions()->sync($allPermissionIds);

            $partialAccessRole->permissions()->sync($viewOnlyIds);

            $cache->flush($fullAccessRole);
            $cache->flush($partialAccessRole);
            $cache->flush();
        });

        Cache::forever('admin-rbac.discovery.hash', $hash);

        return [
            'permissions' => Permission::count(),
            'roles' => Role::count(),
        ];
    }

    public function syncPartialAccessRole(array $modulePermissions): void
    {
        $role = Role::where('slug', 'partial-access')->firstOrFail();
        $permissionIds = Permission::query()
            ->whereIn('slug', $modulePermissions)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);

        app(AdminPermissionCacheService::class)->flush($role);
    }

    public function buildModulePermissionSlugs(string $moduleKey, string $accessLevel): array
    {
        $base = [
            'can_view',
        ];

        if ($accessLevel === 'view_edit') {
            $base = array_merge($base, [
                'can_create',
                'can_update',
                'can_delete',
                'can_status',
            ]);
        }

        return array_map(fn (string $action) => "{$moduleKey}.{$action}", $base);
    }
}
