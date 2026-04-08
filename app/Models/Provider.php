<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Provider extends Authenticatable
{
    use HasApiTokens;

    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'profile_image',
        'id_front',
        'id_back',
        'services',
        'status',
        'password',
        'device_id',
    ];

    protected $casts = [
        'services' => 'array',
    ];
    public function mainCategories()
    {
        return $this->belongsToMany(MainCategory::class, 'provider_skills');
    }

    public function subCategories()
    {
        return $this->belongsToMany(SubCategory::class, 'provider_subcategories', 'provider_id', 'sub_category_id');
    }
    public function bookingRequests()
    {
        return $this->hasMany(BookingRequest::class);
    }
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'provider_id', 'id');
    }
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
}
