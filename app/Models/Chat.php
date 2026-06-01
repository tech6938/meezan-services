<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes;

    protected $table = 'chats';
    protected $fillable = [
        'booking_id',
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

    protected $casts = [
        'is_seen' => 'boolean',
        'seen_at' => 'datetime',
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

    public function scopeForParticipant(Builder $query, array $participant, ?string $role = null): Builder
    {
        $roles = $role ? [$role] : ['sender', 'receiver'];

        return $query->where(function (Builder $outerQuery) use ($participant, $roles) {
            foreach ($roles as $index => $currentRole) {
                $method = $index === 0 ? 'where' : 'orWhere';

                $outerQuery->{$method}(function (Builder $roleQuery) use ($participant, $currentRole) {
                    $roleQuery->where("{$currentRole}_id", $participant['id'])
                        ->where("{$currentRole}_type", $participant['type']);
                });
            }
        });
    }

    public function scopeBetweenParticipants(Builder $query, array $firstParticipant, array $secondParticipant): Builder
    {
        return $query->where(function (Builder $conversationQuery) use ($firstParticipant, $secondParticipant) {
            $conversationQuery->where(function (Builder $directQuery) use ($firstParticipant, $secondParticipant) {
                $directQuery->forParticipant($firstParticipant, 'sender')
                    ->forParticipant($secondParticipant, 'receiver');
            })->orWhere(function (Builder $reverseQuery) use ($firstParticipant, $secondParticipant) {
                $reverseQuery->forParticipant($secondParticipant, 'sender')
                    ->forParticipant($firstParticipant, 'receiver');
            });
        });
    }

    public static function participantKey(array $participant): string
    {
        return $participant['type'] . '_' . $participant['id'];
    }

    public function bookingRequest()
    {
        return $this->belongsTo(BookingRequest::class, 'booking_id');
    }

    /**
     * Delete all chats for a specific conversation that belong to a specific booking
     * Returns true if chats were deleted, false if no chats found or booking has other active chats
     */
    public static function deleteConversationByBooking(array $firstParticipant, array $secondParticipant, $bookingId): array
    {
        $result = [
            'success' => false,
            'deleted_count' => 0,
            'message' => ''
        ];

        // First, check if there are any OTHER active chats (with different booking_id) between these participants
        $otherChats = self::betweenParticipants($firstParticipant, $secondParticipant)
            ->where('booking_id', '!=', $bookingId)
            ->whereNull('deleted_at')
            ->exists();

        if ($otherChats) {
            $result['message'] = 'Cannot delete. There are other active chats between these users for different bookings.';
            return $result;
        }

        // Delete only chats with the specific booking_id
        $deletedCount = self::betweenParticipants($firstParticipant, $secondParticipant)
            ->where('booking_id', $bookingId)
            ->delete();

        if ($deletedCount > 0) {
            $result['success'] = true;
            $result['deleted_count'] = $deletedCount;
            $result['message'] = "Successfully deleted {$deletedCount} chat message(s) for booking #{$bookingId}";
        } else {
            $result['message'] = 'No chats found for this booking.';
        }

        return $result;
    }

    /**
     * Get unique booking IDs for a conversation between participants
     */
    public static function getConversationBookings(array $firstParticipant, array $secondParticipant): \Illuminate\Support\Collection
    {
        return self::betweenParticipants($firstParticipant, $secondParticipant)
            ->whereNotNull('booking_id')
            ->select('booking_id')
            ->distinct()
            ->pluck('booking_id');
    }

    /**
     * Get unread messages count between participants, excluding soft-deleted
     */
    public function unreadConversationQuery(array $firstParticipant, array $secondParticipant): Builder
    {
        return $this->betweenParticipants($firstParticipant, $secondParticipant)
            ->where('receiver_id', $firstParticipant['id'])
            ->where('receiver_type', $firstParticipant['type'])
            ->where('is_seen', false)
            ->whereNull('deleted_at'); // Exclude soft-deleted records
    }
}
