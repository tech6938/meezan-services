<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\Provider;
use App\Models\ShopKeeper;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel($this->typedChannelName()),
            new PrivateChannel($this->legacyChannelName()),
        ];
    }

    public function broadcastWith()
    {
        $sender = $this->chat->relationLoaded('sender')
            ? $this->chat->sender
            : $this->chat->sender()->first();

        return [
            'id' => $this->chat->id,
            'booking_id' => $this->chat->booking_id,
            'sender_id' => $this->chat->sender_id,
            'sender_type' => $this->chat->sender_type,
            'sender_type_alias' => $this->participantTypeAlias($this->chat->sender_type),
            'sender_name' => $this->participantName($sender),
            'sender_image' => $this->participantImage($sender),
            'receiver_id' => $this->chat->receiver_id,
            'receiver_type' => $this->chat->receiver_type,
            'receiver_type_alias' => $this->participantTypeAlias($this->chat->receiver_type),
            'message' => $this->chat->message,
            'file_name' => $this->chat->file_name,
            'file_type' => $this->chat->file_type,
            'file_path' => $this->chat->file_path,
            'is_seen' => (bool) $this->chat->is_seen,
            'created_at' => $this->chat->created_at->toDateTimeString(),
        ];
    }

    private function typedChannelName(): string
    {
        return 'chat.' . $this->participantTypeAlias($this->chat->receiver_type) . '.' . $this->chat->receiver_id;
    }

    private function legacyChannelName(): string
    {
        return 'chat.' . $this->chat->receiver_id;
    }

    private function participantTypeAlias(?string $type): string
    {
        return match ($type) {
            User::class => 'user',
            Provider::class => 'provider',
            ShopKeeper::class => 'shopkeeper',
            default => 'unknown',
        };
    }

    private function participantName($participant): ?string
    {
        if ($participant instanceof User) {
            return $participant->name;
        }

        if ($participant instanceof Provider) {
            return $participant->full_name ?? $participant->name;
        }

        if ($participant instanceof ShopKeeper) {
            return $participant->name;
        }

        return $participant->name ?? null;
    }

    private function participantImage($participant): ?string
    {
        if ($participant instanceof User) {
            return $participant->image_url;
        }

        if ($participant instanceof Provider) {
            return $participant->profile_image_url;
        }

        if ($participant instanceof ShopKeeper) {
            return $participant->profile_image;
        }

        return $participant->image_url ?? $participant->profile_image ?? null;
    }
}
