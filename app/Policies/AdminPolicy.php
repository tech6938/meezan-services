<?php

namespace App\Policies;

use App\Models\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->isSuperAdmin();
    }

    public function view(Admin $admin, Admin $target): bool
    {
        return $admin->isSuperAdmin();
    }

    public function create(Admin $admin): bool
    {
        return $admin->isSuperAdmin();
    }

    public function update(Admin $admin, Admin $target): bool
    {
        return $admin->isSuperAdmin();
    }

    public function delete(Admin $admin, Admin $target): bool
    {
        // Don't allow deleting yourself
        if ($admin->id === $target->id) {
            return false;
        }
        return $admin->isSuperAdmin();
    }
}
