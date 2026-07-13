<?php

namespace App\Console\Commands;

use App\Services\AdminPermissionDiscoveryService;
use Illuminate\Console\Command;

class DebugPermissionDiscovery extends Command
{
    protected $signature = 'admin:debug-discovery {--module= : Filter by module key}';
    protected $description = 'Debug permission discovery to see what routes are being discovered';

    public function handle(): int
    {
        $service = app(AdminPermissionDiscoveryService::class);
        $permissions = $service->discoverPermissions();

        $filter = $this->option('module');

        if ($filter) {
            $permissions = $permissions->filter(fn ($p) => $p['module_key'] === $filter);
            $this->info("Permissions for module: {$filter}");
        } else {
            $this->info("All discovered permissions:");
        }

        $this->info("Total: " . $permissions->count());
        $this->line('');

        $permissions->each(function ($permission) {
            $this->info("Route: " . $permission['route_name']);
            $this->line("  Module: " . $permission['module_key']);
            $this->line("  Action: " . $permission['action']);
            $this->line("  Slug: " . $permission['slug']);
            $this->line("  URI: " . $permission['uri']);
            $this->line("  Methods: " . json_encode($permission['http_methods']));
            $this->line('');
        });

        return self::SUCCESS;
    }
}
