<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_full_access',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_full_access' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withTimestamps();
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
