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
        'file',
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
