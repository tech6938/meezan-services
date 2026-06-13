<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralLog extends Model
{
    protected $fillable = [
        'booking_id',
        'referrer_user_id',
        'referred_user_id',
        'level',
        'referral_code',
        'commission_type',
        'commission_rate',
        'booking_amount',
        'earned_amount',
        'status',
    ];

    protected $casts = [
        'level' => 'integer',
        'commission_rate' => 'decimal:2',
        'booking_amount' => 'decimal:2',
        'earned_amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(BookingRequest::class, 'booking_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
