<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $guarded = [];
    // Who sent this file
    // Sender of the file
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Receiver of the file
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
