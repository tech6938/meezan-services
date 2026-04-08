<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'phone',
        'name',
        'image',
        'password',
        'device_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ✅ IMAGE ACCESSOR (returns full URL)
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) =>
            $value ? asset('storage/' . $value) : null
        );
    }

    // ✅ RELATIONS
    // Files the user sent
    public function sentFiles()
    {
        return $this->hasMany(Upload::class, 'sender_id');
    }

    // Files the user received
    public function receivedFiles()
    {
        return $this->hasMany(Upload::class, 'receiver_id');
    }

    // Messages the user sent
    public function sentMessages()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    // Messages the user received
    public function receivedMessages()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    // User.php

// All service requests made by this user
public function serviceRequests()
{
    return $this->hasMany(ServiceRequest::class, 'user_id');
}

// All bookings associated with the user's service requests
public function bookings()
{
    // through the ServiceRequest model
    return $this->hasManyThrough(
        BookingRequest::class,
        ServiceRequest::class,
        'user_id',      // Foreign key on ServiceRequest table...
        'request_id',   // Foreign key on BookingRequest table...
        'id',           // Local key on User table
        'id'            // Local key on ServiceRequest table
    );
}

}
