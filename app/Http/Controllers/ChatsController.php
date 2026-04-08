<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chat;
use App\Models\Provider;

class ChatsController extends Controller
{
    // List of all chats (you can keep this)
    public function chatsList()
    {
        $data = Chat::with(['sender', 'receiver'])
            ->latest()
            ->paginate(100);

        return view('chat.chatsList', compact('data'));
    }


    // Chat between a user (sender) and provider (receiver)
    public function chatBetween($user_id, $provider_id)
    {
        // Fetch messages between this user and provider
        $messages = Chat::where(function ($q) use ($user_id, $provider_id) {
            $q->where('sender_id', $user_id)
                ->where('receiver_id', $provider_id);
        })->orWhere(function ($q) use ($user_id, $provider_id) {
            $q->where('sender_id', $provider_id)
                ->where('receiver_id', $user_id);
        })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        $sender = User::findOrFail($user_id);
        $receiver = Provider::findOrFail($provider_id);

        return view('chat.chatBetween', compact('messages', 'sender', 'receiver'));
    }
}
