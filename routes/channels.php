<?php

use App\Models\Provider;
use App\Models\ShopKeeper;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{participantType}.{receiverId}', function ($user, $participantType, $receiverId) {
    $models = [
        'user' => User::class,
        'provider' => Provider::class,
        'shopkeeper' => ShopKeeper::class,
    ];

    if (!isset($models[$participantType])) {
        return false;
    }

    return (int) $user->id === (int) $receiverId && get_class($user) === $models[$participantType];
});

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});
