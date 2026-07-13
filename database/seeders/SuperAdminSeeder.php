<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use App\Services\AdminRbacSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $sync = app(AdminRbacSyncService::class);
        $sync->sync();

        $superAdminRole = Role::where('slug', 'full-access')->firstOrFail();

        $name = env('ADMIN_SUPERADMIN_NAME', 'Super Admin');
        $email = env('ADMIN_SUPERADMIN_EMAIL', 'superadmin@example.com');
        $password = env('ADMIN_SUPERADMIN_PASSWORD', 'password');

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role_id' => $superAdminRole->id,
                'is_super_admin' => true,
            ]
        );
    }
}
