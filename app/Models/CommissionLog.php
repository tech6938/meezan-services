<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionLog extends Model
{
    protected $table = 'commission_logs';

    protected $fillable = [
        'booking_id',
        'provider_id',
        'shopkeeper_id',
        'sub_category_id',
        'commission_type',
        'transaction_type',
        'gateway',
        'commission_rate',
        'booking_price',
        'commission_deducted',
        'old_balance',
        'new_balance',
        'status',
        'transaction_reference',
        'response_data',
        'completed_at',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'booking_price' => 'decimal:2',
        'commission_deducted' => 'decimal:2',
        'old_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'response_data' => 'array',
        'completed_at' => 'datetime',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function booking()
    {
        return $this->belongsTo(BookingRequest::class, 'booking_id');
    }
}