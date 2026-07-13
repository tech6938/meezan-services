<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ADMINS ===\n";
$admins = DB::table('admins')->get();
foreach ($admins as $admin) {
    echo "ID: {$admin->id}, Name: {$admin->name}, Super: {$admin->is_super_admin}, Role ID: {$admin->role_id}\n";
}

echo "\n=== ROLES ===\n";
$roles = DB::table('roles')->get();
foreach ($roles as $role) {
    echo "ID: {$role->id}, Name: {$role->name}, Slug: {$role->slug}, Full Access: {$role->is_full_access}, Active: {$role->is_active}\n";
}

echo "\n=== ADMIN-MANAGEMENT PERMISSIONS ===\n";
$perms = DB::table('permissions')->where('module_key', 'admin-management')->get();
foreach ($perms as $perm) {
    echo "ID: {$perm->id}, Slug: {$perm->slug}, Module: {$perm->module_key}, Action: {$perm->action}\n";
}

echo "\n=== FULL-ACCESS ROLE PERMISSIONS ===\n";
$fullAccessRole = DB::table('roles')->where('is_full_access', true)->first();
if ($fullAccessRole) {
    $perms = DB::table('permission_role')
        ->where('role_id', $fullAccessRole->id)
        ->pluck('permission_id')
        ->toArray();
    echo "Full Access Role ID: {$fullAccessRole->id}\n";
    echo "Permission Count: " . count($perms) . "\n";

    $adminManagementPerms = DB::table('permissions')
        ->where('module_key', 'admin-management')
        ->pluck('id')
        ->toArray();
    echo "Admin-Management Permission IDs: " . implode(', ', $adminManagementPerms) . "\n";
    echo "Are admin-management perms assigned? " . (count(array_intersect($perms, $adminManagementPerms)) > 0 ? 'YES' : 'NO') . "\n";
}
