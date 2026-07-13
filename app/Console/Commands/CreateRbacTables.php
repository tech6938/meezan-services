<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRbacTables extends Command
{
    protected $signature = 'admin:create-tables';
    protected $description = 'Create RBAC tables if they do not exist';

    public function handle(): int
    {
        if (!Schema::hasTable('roles')) {
            $this->info('Creating roles table...');
            DB::statement("
                CREATE TABLE IF NOT EXISTS `roles` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `name` varchar(255) NOT NULL,
                    `slug` varchar(255) NOT NULL UNIQUE,
                    `description` text,
                    `is_full_access` boolean NOT NULL DEFAULT false,
                    `is_active` boolean NOT NULL DEFAULT true,
                    `created_at` timestamp NULL,
                    `updated_at` timestamp NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->info('✓ roles table created');
        } else {
            $this->info('✓ roles table already exists');
        }

        if (!Schema::hasTable('permissions')) {
            $this->info('Creating permissions table...');
            DB::statement("
                CREATE TABLE IF NOT EXISTS `permissions` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `module_key` varchar(255) NOT NULL,
                    `module_label` varchar(255) NOT NULL,
                    `action` varchar(255) NOT NULL,
                    `slug` varchar(255) NOT NULL UNIQUE,
                    `route_name` varchar(255),
                    `uri` varchar(255),
                    `http_methods` json,
                    `description` text,
                    `is_active` boolean NOT NULL DEFAULT true,
                    `created_at` timestamp NULL,
                    `updated_at` timestamp NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->info('✓ permissions table created');
        } else {
            $this->info('✓ permissions table already exists');
        }

        if (!Schema::hasTable('permission_role')) {
            $this->info('Creating permission_role table...');
            DB::statement("
                CREATE TABLE IF NOT EXISTS `permission_role` (
                    `permission_id` bigint unsigned NOT NULL,
                    `role_id` bigint unsigned NOT NULL,
                    `created_at` timestamp NULL,
                    `updated_at` timestamp NULL,
                    PRIMARY KEY (`permission_id`, `role_id`),
                    UNIQUE KEY `permission_role_unique` (`role_id`, `permission_id`),
                    CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
                    CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->info('✓ permission_role table created');
        } else {
            $this->info('✓ permission_role table already exists');
        }

        // Add columns to admins table if missing
        if (!Schema::hasColumn('admins', 'role_id')) {
            $this->info('Adding role_id column to admins table...');
            DB::statement("
                ALTER TABLE `admins` ADD COLUMN `role_id` bigint unsigned AFTER `id`,
                ADD CONSTRAINT `admins_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
            ");
            $this->info('✓ role_id column added');
        }

        if (!Schema::hasColumn('admins', 'is_super_admin')) {
            $this->info('Adding is_super_admin column to admins table...');
            DB::statement("ALTER TABLE `admins` ADD COLUMN `is_super_admin` boolean DEFAULT false AFTER `role_id`");
            $this->info('✓ is_super_admin column added');
        }

        $this->info('✓ All RBAC tables created successfully!');
        return self::SUCCESS;
    }
}
