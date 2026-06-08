<?php

namespace App\Services;

use App\Models\FCMToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmTokenService
{
    protected Messaging $messaging;
    protected ?string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = env('FIREBASE_CREDENTIALS');

        if (!$this->credentialsPath || !file_exists($this->credentialsPath)) {
            Log::error('Firebase credentials file not found at: ' . $this->credentialsPath);
            throw new \Exception('Firebase credentials file not found');
        }

        $factory = (new Factory)->withServiceAccount($this->credentialsPath);
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Save or update user's FCM token
     */
    public function saveToken($entityId, $entityType, string $fcmToken): bool
    {
        try {
            FCMToken::updateOrCreate(
                [
                    'entity_id' => $entityId,
                    'entity_type' => $entityType
                ],
                [
                    'fcm_token' => $fcmToken
                ]
            );
            Log::info("FCM token saved for {$entityType} ID: {$entityId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Error saving FCM token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove user's FCM token (when they logout or uninstall)
     */
    public function removeToken($entityId, $entityType): bool
    {
        try {
            FCMToken::where('entity_id', $entityId)
                ->where('entity_type', $entityType)
                ->delete();
            Log::info("FCM token removed for {$entityType} ID: {$entityId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Error removing FCM token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tokens by entity type
     */
    public function getTokensByEntityType($entityType): array
    {
        return FCMToken::where('entity_type', $entityType)
            ->pluck('fcm_token')
            ->toArray();
    }

    /**
     * Get tokens for specific entities
     */
    public function getTokensForEntities($entityIds, $entityType): array
    {
        return FCMToken::where('entity_type', $entityType)
            ->whereIn('entity_id', $entityIds)
            ->pluck('fcm_token')
            ->toArray();
    }

    /**
     * Send notification to single device
     */
    // public function sendNotification($deviceToken, $title, $body, $data = []): ?array
    // {
    //     if (empty($deviceToken)) {
    //         Log::warning('FCM token empty, skipping notification');
    //         return [
    //             'success' => false,
    //             'error' => 'FCM token empty',
    //         ];
    //     }

    //     try {
    //         $message = CloudMessage::withTarget('token', $deviceToken)
    //             ->withNotification(Notification::create($title, $body))
    //             ->withData(array_map('strval', $data));

    //         $result = $this->messaging->send($message);
    //         Log::info('FCM Notification sent successfully for token: ' . $deviceToken);
            
    //         return [
    //             'success' => true,
    //             'result' => $result
    //         ];
    //     } catch (MessagingException | FirebaseException $e) {
    //         Log::error('FCM Send Error: ' . $e->getMessage());
            
    //         // If token is invalid, remove it from database
    //         if (str_contains($e->getMessage(), 'Invalid registration token')) {
    //             FCMToken::where('fcm_token', $deviceToken)->delete();
    //             Log::info('Removed invalid FCM token from database');
    //         }
            
    //         return [
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ];
    //     }
    // }

    /**
     * Send notification to single device
     */
    public function sendNotification($deviceToken, $title, $body, $data = []): ?array
    {
        if (empty($deviceToken)) {
            Log::warning('FCM token empty, skipping notification');
            return [
                'success' => false,
                'error' => 'FCM token empty',
            ];
        }

        try {
            // Add more data to help debugging
            $enhancedData = array_merge($data, [
                'title' => $title,
                'body' => $body,
                'timestamp' => now()->toString(),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification(Notification::create($title, $body))
                ->withData(array_map('strval', $enhancedData));

            $result = $this->messaging->send($message);

            // Log more details
            Log::info('FCM Notification sent successfully', [
                'token' => substr($deviceToken, 0, 30) . '...',
                'title' => $title,
                'message_id' => $result,
                'full_response' => $result // This will help debug
            ]);

            return [
                'success' => true,
                'result' => $result
            ];
        } catch (MessagingException | FirebaseException $e) {
            Log::error('FCM Send Error: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'Invalid registration token')) {
                FCMToken::where('fcm_token', $deviceToken)->delete();
                Log::info('Removed invalid FCM token from database');
            }

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to multiple devices
     */
    public function sendToMultiple($tokens, $title, $body, $data = []): ?array
    {
        if (empty($tokens)) {
            Log::warning('No FCM tokens provided for multicast');
            return ['success' => false, 'message' => 'No tokens provided'];
        }

        try {
            $message = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ];

            $result = $this->messaging->sendMulticast($message, $tokens);

            Log::info('FCM Multicast sent', [
                'success_count' => $result->successes()->count(),
                'failure_count' => $result->failures()->count()
            ]);

            // Clean up invalid tokens
            foreach ($result->failures() as $failure) {
                if (str_contains($failure->error()->getMessage(), 'Invalid registration token')) {
                    FCMToken::where('fcm_token', $failure->target()->value())->delete();
                }
            }

            return [
                'success' => true,
                'success_count' => $result->successes()->count(),
                'failure_count' => $result->failures()->count(),
                'result' => $result
            ];
        } catch (MessagingException | FirebaseException $e) {
            Log::error('FCM Multicast Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send notification to all users
     */
    public function sendToAllUsers($title, $body, $data = []): ?array
    {
        $tokens = $this->getTokensByEntityType('user');

        if (empty($tokens)) {
            Log::info('No users have FCM tokens registered');
            return ['success' => false, 'message' => 'No users with FCM tokens'];
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send notification to all providers
     */
    public function sendToAllProviders($title, $body, $data = []): ?array
    {
        $tokens = $this->getTokensByEntityType('provider');

        if (empty($tokens)) {
            Log::info('No providers have FCM tokens registered');
            return ['success' => false, 'message' => 'No providers with FCM tokens'];
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send notification to all shopkeepers
     */
    public function sendToAllShopkeepers($title, $body, $data = []): ?array
    {
        $tokens = $this->getTokensByEntityType('shopkeeper');

        if (empty($tokens)) {
            Log::info('No shopkeepers have FCM tokens registered');
            return ['success' => false, 'message' => 'No shopkeepers with FCM tokens'];
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send notification to specific users by their IDs
     */
    public function sendToSpecificUsers($userIds, $title, $body, $data = []): ?array
    {
        $tokens = $this->getTokensForEntities($userIds, 'user');

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No valid tokens found for specified users'];
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send notification to specific providers by their IDs
     */
    public function sendToSpecificProviders($providerIds, $title, $body, $data = []): ?array
    {
        $tokens = $this->getTokensForEntities($providerIds, 'provider');

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No valid tokens found for specified providers'];
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send notification based on service request
     */
    public function notifyNewServiceRequest($serviceRequest, $user): ?array
    {
        $title = 'New Service Request Created!';
        $body = $user->name . ' created a new service request';
        $data = [
            'request_id' => (string)$serviceRequest->id,
            'type' => 'new_service_request',
            'user_id' => (string)$user->id,
            'user_name' => $user->name,
            'action' => 'view_request'
        ];

        // Send to all providers
        return $this->sendToAllProviders($title, $body, $data);
    }

    /**
     * Send notification for new booking request
     */
    public function notifyNewBookingRequest($bookingRequest, $provider): ?array
    {
        $title = 'New Booking Request!';
        $body = 'You have a new booking request from ' . $bookingRequest->user->name;
        $data = [
            'booking_id' => (string)$bookingRequest->id,
            'type' => 'new_booking',
            'provider_id' => (string)$provider->id,
            'amount' => (string)$bookingRequest->amount,
            'action' => 'view_booking'
        ];

        // Send to specific provider
        return $this->sendToSpecificProviders([$provider->id], $title, $body, $data);
    }

    /**
     * Send notification for booking status update
     */
    public function notifyBookingStatusUpdate($bookingRequest, $user): ?array
    {
        $title = 'Booking Status Updated';
        $body = 'Your booking status has been updated to: ' . $bookingRequest->status;
        $data = [
            'booking_id' => (string)$bookingRequest->id,
            'type' => 'booking_status_update',
            'status' => $bookingRequest->status,
            'action' => 'view_booking'
        ];

        // Send to specific user
        return $this->sendToSpecificUsers([$user->id], $title, $body, $data);
    }

    /**
     * Send notification for file upload
     */
    public function notifyFileUpload($upload, $receiver): ?array
    {
        $title = 'New File Received';
        $body = 'You have received a new file from ' . $upload->sender->name;
        $data = [
            'file_id' => (string)$upload->id,
            'type' => 'new_file',
            'sender_name' => $upload->sender->name,
            'action' => 'view_file'
        ];

        return $this->sendToSpecificUsers([$receiver->id], $title, $body, $data);
    }
}
