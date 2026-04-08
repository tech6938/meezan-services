<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    protected $table = 'booking_requests';

    protected $fillable = [
        'provider_id',
        'shopkeeper_id',
        'user_id',
        'request_id',
        'order_no',
        'price',
        'cash_on_delivery',
        'payment_type',
        'req_status',
        'status',
        'cancel_by',
        'cancel_reason',
        'assigned',
        'goto',
        'details',
        'audio',
        'is_seen',
        'seen_at',
    ];

    protected $casts = [
        'price' => 'float',
        'cash_on_delivery' => 'integer',
        'assigned' => 'integer',
        'goto' => 'integer',
        'is_seen' => 'integer',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class, 'provider_id', 'provider_id')
            ->where('user_id', $this->user_id);
    }

    public function shopkeeper()
    {
        return $this->belongsTo(ShopKeeper::class, 'shopkeeper_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    // Accessor for audio - returns full URL
    public function getAudioAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // Check if it's already a full URL
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Return full URL for local files
        return asset($value);
    }

    // Mutator for audio - you can add logic here if needed
    public function setAudioAttribute($value)
    {
        $this->attributes['audio'] = $value;
    }
}
