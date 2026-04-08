<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class ShopKeeper extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'shop_keepers';
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'id_front',
        'id_back',
        'profile_image',
        'device_id',
        'password',
    ];

    // Accessors to get full URL
    public function getIdFrontAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function getIdBackAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function getProfileImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
    public function shops()
    {
        return $this->hasMany(Shop::class, 'shopkeeper_id');
    }


    public function ratings()
    {
        return $this->hasMany(Rating::class, 'shopkeeper_id');
    }

    public function bookingRequests()
    {
        return $this->hasMany(BookingRequest::class, 'shopkeeper_id');
    }
}
