<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    protected $fillable = [
        'sender_id',
        'sender_type',
        'receiver_id',
        'receiver_type',
        'message',
        'file_name',
        'file_type',
        'file_path',
        'is_seen',
        'seen_at',
    ];

    public function sender()
    {
        return $this->morphTo();
    }

    public function receiver()
    {
        return $this->morphTo();
    }

    // Scope for unread messages
    public function scopeUnread($query, $userId, $userType)
    {
        return $query->where('receiver_id', $userId)
            ->where('receiver_type', $userType)
            ->where('is_seen', false);
    }
}
