<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'module_key',
        'module_label',
        'action',
        'slug',
        'route_name',
        'uri',
        'http_methods',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'http_methods' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
