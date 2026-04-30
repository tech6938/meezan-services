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

    protected $appends = [
        'profile_image_url',
        'id_front_url',
        'id_back_url',
    ];

    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image
            ? url('profiles/' . $this->profile_image)
            : null;
    }

    public function getIdFrontUrlAttribute()
    {
        return $this->id_front
            ? url('documents/' . $this->id_front)
            : null;
    }

    public function getIdBackUrlAttribute()
    {
        return $this->id_back
            ? url('documents/' . $this->id_back)
            : null;
    }
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

    public function requestSeens()
    {
        return $this->hasMany(ProviderRequestSeen::class, 'provider_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
}
