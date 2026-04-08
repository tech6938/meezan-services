<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopServiceRequest extends Model
{
    protected $table = 'shop_service_requests';

    protected $guarded = [];

    protected $appends = ['file_type'];

    /**
     * Accessor for 'file' attribute
     * Returns array of file URLs
     */
    public function getFileAttribute($value)
    {
        if (!$value) {
            return [];
        }

        $files = [];

        // If it's a JSON array string
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $files = $decoded;
            } else {
                // Treat as single file string
                $files = [$value];
            }
        } elseif (is_array($value)) {
            $files = $value;
        }

        // Clean file paths and generate full URLs
        return array_map(function ($file) {
            $file = trim($file, '\\"[]');
            return url($file);
        }, $files);
    }

    /**
     * Optional: File type accessor for first file
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

        $audio = ['mp3', 'wav', 'ogg', 'm4a'];
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

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shopBookingRequest()
    {
        return $this->hasOne(BookingRequest::class, 'request_id');
    }
}
