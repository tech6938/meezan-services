<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_super_admin' => 'boolean',
        ];
    }

    public function assignedRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hasRole(string $slug): bool
    {
        return $this->assignedRole?->slug === $slug;
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function hasPermission(string $moduleKey, string $action = 'can_view'): bool
    {
        return app(\App\Services\AdminAuthorizationService::class)->allows($this, $moduleKey, $action);
    }

    public function canViewModule(string $moduleKey): bool
    {
        return $this->hasPermission($moduleKey, 'can_view');
    }
}
