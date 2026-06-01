<?php

namespace App\Traits;

use App\Models\Chat;

trait HasUnreadMessages
{
    /**
     * Get unread message count between two users for a specific booking
     */
    public function getUnreadCountForBooking($userId, $userType, $bookingId)
    {
        return Chat::where('booking_id', $bookingId)
            ->where('receiver_id', $userId)
            ->where('receiver_type', $userType)
            ->where('is_seen', false)
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Get unread message count for provider in a booking
     */
    public function getProviderUnreadCount($bookingId, $providerId)
    {
        return Chat::where('booking_id', $bookingId)
            ->where('receiver_id', $providerId)
            ->where('receiver_type', 'App\Models\Provider')
            ->where('is_seen', false)
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Get unread message count for user in a booking
     */
    public function getUserUnreadCount($bookingId, $userId)
    {
        return Chat::where('booking_id', $bookingId)
            ->where('receiver_id', $userId)
            ->where('receiver_type', 'App\Models\User')
            ->where('is_seen', false)
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Get unread message count for shopkeeper in a booking
     */
    public function getShopkeeperUnreadCount($bookingId, $shopkeeperId)
    {
        return Chat::where('booking_id', $bookingId)
            ->where('receiver_id', $shopkeeperId)
            ->where('receiver_type', 'App\Models\ShopKeeper')
            ->where('is_seen', false)
            ->whereNull('deleted_at')
            ->count();
    }
}
