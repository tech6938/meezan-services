<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Previous extends Model
{
    protected $table = 'previouses';
    protected $fillable = ['provider_id', 'images'];
    // Cast images column to array automatically
    protected $casts = [
        'images' => 'array',
    ];

    // Accessor to get full URLs of images
    public function getImagesAttribute($value)
    {
        // Ensure we have an array
        $images = is_array($value) ? $value : json_decode($value, true);

        if (!$images || !is_array($images)) {
            return [];
        }

        return array_map(function ($image) {
            return asset(Storage::url($image));
        }, $images);
    }
    // provider
    public function provider(){
       return $this->belongsTo(Provider::class,'provider_id');
    }
}
