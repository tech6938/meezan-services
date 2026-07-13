<?php

namespace App\Console\Commands;

use App\Services\AdminPermissionCacheService;
use App\Services\AdminRbacSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncAdminRbac extends Command
{
    protected $signature = 'admin:rbac-sync {--clear-cache : Clear cache before syncing}';
    protected $description = 'Sync admin RBAC system - discover permissions and assign to roles';

    public function handle(): int
    {
        if ($this->option('clear-cache')) {
            $this->info('Clearing RBAC cache...');
            Cache::forget('admin-rbac.discovery.hash');
            app(AdminPermissionCacheService::class)->flush();
            $this->info('Cache cleared.');
        }

        $this->info('Syncing admin RBAC system...');

        try {
            $result = app(AdminRbacSyncService::class)->sync();

            $this->info('✓ RBAC sync completed successfully!');
            $this->info("  - Permissions: {$result['permissions']}");
            $this->info("  - Roles: {$result['roles']}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ RBAC sync failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
