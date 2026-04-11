<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';
    protected $hidden = ['updated_at'];
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function shopkeeper()
    {
        return $this->belongsTo(ShopKeeper::class);
    }

    // Helper method to check if balance is sufficient
    public function hasSufficientBalance($amount)
    {
        return $this->amount >= $amount;
    }
}
