<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'whatsapp',
        'app_url',
        'partner_agreement',
        'privacy_policy_partner',
        'termsConditions_partner',
        'privacy_policy_customer',
        'termsConditions_customer',
        'about_us',
        'contact_us',
        'website_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'facebook_url',
        'customer_video_tutorial_url',
        'provider_video_tutorial_url',
        'appIsOn',
        'userAppIsOn',
        'providerAppIsOn',
    ];
}
