<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Role;

class RolePolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->isSuperAdmin() || $admin->assignedRole?->is_full_access;
    }

    public function view(Admin $admin, Role $role): bool
    {
        return $this->viewAny($admin);
    }

    public function create(Admin $admin): bool
    {
        return $this->viewAny($admin);
    }

    public function update(Admin $admin, Role $role): bool
    {
        return $this->viewAny($admin);
    }
}
