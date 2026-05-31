<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'whatsapp',
        'app_url',
        'privacy_policy',
        'website_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'facebook_url',
        'terms_and_conditions_url',
        'customer_video_tutorial_url',
        'provider_video_tutorial_url',
        'appIsOn',
        'userAppIsOn',
        'providerAppIsOn',
    ];
}
