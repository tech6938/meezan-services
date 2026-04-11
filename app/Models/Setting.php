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
        'appIsOn',
    ];
}
