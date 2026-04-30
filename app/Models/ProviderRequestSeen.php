<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderRequestSeen extends Model
{
    protected $table = 'provider_request_seens';

    protected $fillable = [
        'request_id',
        'provider_id',
        'is_seen',
        'seen_at',
    ];

    protected $casts = [
        'is_seen' => 'boolean',
        'seen_at' => 'datetime',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}
