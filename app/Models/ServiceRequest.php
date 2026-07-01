<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $table = 'service_requests';

    protected $fillable = [
        'user_id',
        'cat_id',
        'subcat_id',
        'address_id',
        'shop_id',
        'lang',
        'lat',
        'desc',
        'file',
        'status',
    ];

    protected $appends = ['file_type'];

    /**
     * Mutator for 'file' attribute - converts array to JSON before saving
     */
    public function setFileAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['file'] = json_encode($value);
        } else {
            $this->attributes['file'] = $value;
        }
    }

    /**
     * Accessor for 'file' attribute - converts JSON back to array with full URLs
     */
    public function getFileAttribute($value)
    {
        if (!$value) {
            return [];
        }

        // Decode JSON
        $files = json_decode($value, true);

        if (!is_array($files)) {
            $files = [$value];
        }

        // Generate full URLs
        return array_map(function ($file) {
            $file = trim($file);
            $file = str_replace('\/', '/', $file);
            return url($file);
        }, $files);
    }

    /**
     * File type accessor for first file
     */
    public function getFileTypeAttribute()
    {
        $files = $this->file;
        if (empty($files)) {
            return null;
        }

        $firstFile = $files[0];
        $extension = pathinfo($firstFile, PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        $audio = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];
        $video = ['mp4', 'webm', 'avi', 'mov', 'mkv'];
        $image = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        if (in_array($extension, $audio)) return 'audio';
        if (in_array($extension, $video)) return 'video';
        if (in_array($extension, $image)) return 'image';

        return 'other';
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(MainCategory::class, 'cat_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcat_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function bookingRequest()
    {
        return $this->hasOne(BookingRequest::class, 'request_id');
    }

    public function bookingRequests()
    {
        return $this->hasMany(BookingRequest::class, 'request_id');
    }

    public function providerSeens()
    {
        return $this->hasMany(ProviderRequestSeen::class, 'request_id');
    }

 /**
     * Get all booking requests with their providers
     */
    public function bookingsWithProviders()
    {
        return $this->hasMany(BookingRequest::class, 'request_id')->with('provider');
    }

    /**
     * Get all providers who have bid on this request (pending status)
     * Using req_status = 'accept' but not assigned yet
     */
    public function biddedProviders()
    {
        return $this->belongsToMany(Provider::class, 'booking_requests', 'request_id', 'provider_id')
            ->withPivot('req_status', 'status', 'created_at', 'order_no', 'price', 'goto', 'assigned')
            ->wherePivot('req_status', 'accept')
            ->wherePivot('assigned', 0);
    }

    /**
     * Get all providers who have been accepted for this request
     * Using req_status = 'accept' AND assigned = 1 AND goto = 1
     */
    public function acceptedProviders()
    {
        return $this->belongsToMany(Provider::class, 'booking_requests', 'request_id', 'provider_id')
            ->withPivot('req_status', 'status', 'created_at', 'order_no', 'price', 'goto', 'assigned')
            ->wherePivot('req_status', 'accept')
            ->wherePivot('assigned', 1)
            ->wherePivot('goto', 1);
    }

    /**
     * Get all providers who have completed this request
     */
    public function completedProviders()
    {
        return $this->belongsToMany(Provider::class, 'booking_requests', 'request_id', 'provider_id')
            ->withPivot('req_status', 'status', 'created_at', 'order_no', 'price', 'goto', 'assigned')
            ->wherePivot('req_status', 'accept')
            ->wherePivot('assigned', 1)
            ->wherePivot('status', 'complete_booking');
    }

    /**
     * Get all providers who have cancelled this request
     */
    public function cancelledProviders()
    {
        return $this->belongsToMany(Provider::class, 'booking_requests', 'request_id', 'provider_id')
            ->withPivot('req_status', 'status', 'created_at', 'order_no', 'price', 'goto', 'assigned')
            ->wherePivot('req_status', 'accept')
            ->wherePivot('status', 'cancel');
    }

    /**
     * Get all providers for this request (any status)
     */
    public function allProviders()
    {
        return $this->belongsToMany(Provider::class, 'booking_requests', 'request_id', 'provider_id')
            ->withPivot('req_status', 'status', 'created_at', 'order_no', 'price', 'goto', 'assigned');
    }
}
