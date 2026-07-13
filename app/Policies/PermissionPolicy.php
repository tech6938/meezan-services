<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Permission;

class PermissionPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->isSuperAdmin() || $admin->assignedRole?->is_full_access;
    }

    public function view(Admin $admin, Permission $permission): bool
    {
        return $this->viewAny($admin);
    }
}
