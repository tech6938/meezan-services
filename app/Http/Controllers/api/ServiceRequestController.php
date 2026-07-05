<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\Chat;
use App\Models\Provider;
use App\Models\ServiceRequest;
use App\Models\Shop;
use App\Models\ShopKeeper;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;

class ServiceRequestController extends Controller
{
    // get user all requests

    public function ServiceRequest()
    {
        try {
            $user_id = Auth::user()->id;

            $serviceRequests = ServiceRequest::with([
                'category:id,name',
                'subCategory:id,name',
                'bookingRequests.provider:id,full_name',
                'bookingRequests.shopkeeper:id,name',
                'shop:id,shop_name,category',
            ])
                ->where('user_id', $user_id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($request) {

                    // Define status priority based on req_status (higher number = higher priority)
                    $statusPriority = [
                        'complete' => 5,      // Use 'complete' from req_status
                        'in_progress' => 4,
                        'accept' => 3,
                        'pending' => 2,
                        'reject' => 1,
                        'cancelled' => 0,
                    ];

                    // Find the booking with highest priority req_status
                    $highestPriorityBooking = null;
                    $highestPriority = -1;

                    foreach ($request->bookingRequests as $booking) {
                        // Use req_status directly instead of status column
                        $bookingStatus = $booking->req_status;
                        $priority = $statusPriority[$bookingStatus] ?? 0;

                        if ($priority > $highestPriority) {
                            $highestPriority = $priority;
                            $highestPriorityBooking = $booking;
                        }
                    }

                    // If no booking exists, use service request status
                    $booking = $highestPriorityBooking;
                    // Use req_status from booking, not the status column
                    $status = $booking ? $booking->req_status : $request->status;

                    // Normalize status for API response (if needed)
                    $status = $this->normalizeStatus($status);

                    return [
                        'id' => $request->id,
                        'cat_name' => optional($request->category)->name,
                        'subcat_name' => optional($request->subCategory)->name,
                        'shop_name' => optional($request->shop)->shop_name,
                        'shop_cat' => optional($request->shop)->category,
                        'desc' => $request->desc,
                        'status' => $status,
                        'created_at' => $this->formatApiDateTime($request->created_at),
                        'provider_name' => optional(optional($booking)->provider)->full_name,
                        'shopkeeper_name' => optional(optional($booking)->shopkeeper)->name,
                        'total_bids' => $request->bookingRequests->count(),
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Service requests fetched successfully',
                'data' => $serviceRequests
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // post api
    public function ServiceRequestStore(Request $request)
    {
        try {
            // Validate request with 'file' as array
            $validatedData = $request->validate([
                'cat_id' => 'nullable|integer',
                'subcat_id' => 'nullable|integer',
                'address_id' => 'nullable|integer',
                'shop_id' => 'nullable|integer',
                'lang' => 'required|string',
                'lat' => 'required|string',
                'desc' => 'nullable|string',
                'file' => 'nullable|array',
                'file.*' => 'file|max:102400',
                'status' => 'nullable|string',
            ]);

            $filePaths = [];
            if ($request->hasFile('file')) {   // use 'file' here (same as validation)
                foreach ($request->file('file') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $uploadDir = public_path('uploads');

                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $file->move($uploadDir, $fileName);
                    $filePaths[] = 'uploads/' . $fileName;
                }
            }

            $validatedData['file'] = json_encode($filePaths);

            $validatedData['user_id'] = $request->user()->id;

            $serviceRequest = ServiceRequest::create($validatedData);

            // Send notification
            $this->sendNewOrderNotificationToPartners($serviceRequest);

            return response()->json([
                'status' => true,
                'message' => 'Service request created successfully',
                'data' => $serviceRequest,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // update status
    public function updateStatus(Request $request, $id)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'status' => 'required|string',
            ]);

            // Find the service request
            $serviceRequest = ServiceRequest::find($id);

            if (!$serviceRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service request not found',
                ], 404);
            }

            // Get ALL bookings for this request
            $allBookings = BookingRequest::where('request_id', $id)->get();

            $newStatus = $validatedData['status'];

            // Update service request status
            $serviceRequest->status = $newStatus;
            $serviceRequest->save();

            // ✅ Fix: Use 'cancelled' instead of 'cancel'
            if ($newStatus == 'cancel') {

                // Scenario 1: No providers accepted yet
                if ($allBookings->isEmpty()) {
                    $this->sendNotificationToUser(
                        $serviceRequest->user_id,
                        'Service Request Cancelled ❌',
                        'Your service request has been cancelled',
                        'cancel_request',
                        ['request_id' => (string)$id, 'status' => 'cancel']
                    );
                }
                // Scenario 2: Providers have accepted
                else {
                    foreach ($allBookings as $booking) {
                        $booking->req_status = 'cancelled';
                        $booking->assigned = 0;
                        $booking->goto = 0;
                        $booking->save();

                        $this->sendNotificationToUser(
                            $serviceRequest->user_id,
                            'Service Request Cancelled ❌',
                            'Your service request has been cancelled',
                            'cancel_request',
                            ['request_id' => (string)$id, 'status' => 'cancelled']
                        );

                        if ($booking->provider_id) {
                            $this->sendNotificationToPartner(
                                $booking->provider_id,
                                'provider',
                                'Service Request Cancelled ❌',
                                'The service request has been cancelled',
                                'cancel_booking',
                                ['booking_id' => (string)$booking->id, 'request_id' => (string)$id]
                            );
                        }
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Status updated successfully',
                    'data' => ['status' => 'cancelled']
                ], 200);
            }

            // For non-cancellation status updates
            if ($allBookings->isNotEmpty()) {
                foreach ($allBookings as $booking) {
                    $booking->req_status = $newStatus;
                    $booking->save();
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Status updated successfully',
                'data' => ['status' => $newStatus],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // request details for users

    public function serviceRequestDetails($id)
    {
        try {
            $user_id = Auth::id();

            $serviceRequest = ServiceRequest::with([
                'category:id,name',
                'subCategory:id,name',
                'shop:id,shop_name,category',
                'address:id,name,street,city,PostalCode',
                'bookingRequests.provider.ratings',
                'bookingRequests.shopkeeper.ratings',
            ])
                ->where('user_id', $user_id)
                ->find($id);

            if (!$serviceRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service request not found'
                ], 404);
            }

            $address = null;
            if ($serviceRequest->address) {
                $address = collect([
                    $serviceRequest->address->name,
                    $serviceRequest->address->street,
                    $serviceRequest->address->city,
                    $serviceRequest->address->PostalCode,
                ])->filter()->implode(', ');
            }

            $providersList = [];
            $shopkeepersList = [];

            // Initialize unread counts
            $totalUserUnreadCount = 0;
            $totalProviderUnreadCount = 0;
            $totalShopkeeperUnreadCount = 0;

            // Show ALL booking requests
            foreach ($serviceRequest->bookingRequests as $bookingRequest) {
                // Provider data
                if ($bookingRequest->provider) {
                    $provider = $bookingRequest->provider;

                    // Calculate unread counts
                    $providerUnreadCount = Chat::where('booking_id', $bookingRequest->id)
                        ->where('receiver_id', $provider->id)
                        ->where('receiver_type', 'App\Models\Provider')
                        ->where('is_seen', false)
                        ->whereNull('deleted_at')
                        ->count();

                    $userUnreadCount = Chat::where('booking_id', $bookingRequest->id)
                        ->where('receiver_id', $user_id)
                        ->where('receiver_type', 'App\Models\User')
                        ->where('is_seen', false)
                        ->whereNull('deleted_at')
                        ->count();

                    $totalProviderUnreadCount += $providerUnreadCount;
                    $totalUserUnreadCount += $userUnreadCount;

                    $totalCompletedBookings = $provider->bookingRequests()
                        ->where('req_status', 'complete')
                        ->count();

                    $averageRating = $provider->ratings()->avg('rating');

                    $providersList[] = [
                        'id' => $provider->id,
                        'goto' => $bookingRequest->goto,
                        'name' => $provider->full_name,
                        'profile_image' => $provider->profile_image
                            ? url('profiles/' . $provider->profile_image)
                            : null,
                        'total_completed_bookings' => $totalCompletedBookings,
                        'average_rating' => $averageRating ? round($averageRating, 1) : 0,
                        'status' => $this->normalizeStatus($bookingRequest->req_status),
                        'unread_count' => $providerUnreadCount,
                        'booking_id' => $bookingRequest->id,
                        'assigned' => $bookingRequest->assigned,
                        'price' => $bookingRequest->price,
                        'payment_type' => $bookingRequest->payment_type,
                        'created_at' => $bookingRequest->created_at,
                    ];
                }

                // Shopkeeper data
                if ($bookingRequest->shopkeeper) {
                    $shopkeeper = $bookingRequest->shopkeeper;

                    // Calculate unread counts
                    $shopkeeperUnreadCount = Chat::where('booking_id', $bookingRequest->id)
                        ->where('receiver_id', $shopkeeper->id)
                        ->where('receiver_type', 'App\Models\ShopKeeper')
                        ->where('is_seen', false)
                        ->whereNull('deleted_at')
                        ->count();

                    // Only add user unread count if not already added by provider
                    if (!$bookingRequest->provider) {
                        $userUnreadCount = Chat::where('booking_id', $bookingRequest->id)
                            ->where('receiver_id', $user_id)
                            ->where('receiver_type', 'App\Models\User')
                            ->where('is_seen', false)
                            ->whereNull('deleted_at')
                            ->count();
                        $totalUserUnreadCount += $userUnreadCount;
                    }

                    $totalShopkeeperUnreadCount += $shopkeeperUnreadCount;

                    $totalCompletedBookings = $shopkeeper->bookingRequests()
                        ->where('req_status', 'complete')
                        ->count();

                    $averageRating = $shopkeeper->ratings()->avg('rating');

                    $shopkeepersList[] = [
                        'id' => $shopkeeper->id,
                        'name' => $shopkeeper->name,
                        'profile_image' => $shopkeeper->profile_image ?? null,
                        'total_completed_bookings' => $totalCompletedBookings,
                        'average_rating' => $averageRating ? round($averageRating, 1) : 0,
                        'status' => $this->normalizeStatus($bookingRequest->req_status),
                        'unread_count' => $shopkeeperUnreadCount,
                        'booking_id' => $bookingRequest->id,
                        'assigned' => $bookingRequest->assigned,
                        'price' => $bookingRequest->price,
                        'payment_type' => $bookingRequest->payment_type,
                        'created_at' => $bookingRequest->created_at,
                    ];
                }
            }

            // For the main status - show the highest priority based on req_status
            $statusPriority = [
                'complete' => 5,
                'in_progress' => 4,
                'accept' => 3,
                'pending' => 2,
                'reject' => 1,
                'cancelled' => 0,
            ];

            $highestPriorityStatus = 'pending';
            $highestPriority = 0;

            foreach ($serviceRequest->bookingRequests as $booking) {
                $bookingStatus = $booking->req_status;
                $priority = $statusPriority[$bookingStatus] ?? 0;
                if ($priority > $highestPriority) {
                    $highestPriority = $priority;
                    $highestPriorityStatus = $bookingStatus;
                }
            }

            $mainStatus = $highestPriority > 0 ? $highestPriorityStatus : $serviceRequest->status;
            $mainStatus = $this->normalizeStatus($mainStatus);

            return response()->json([
                'status' => true,
                'message' => 'Service request details retrieved successfully',
                'data' => [
                    'id' => $serviceRequest->id,
                    'user_id' => $serviceRequest->user_id,
                    'cat_name' => optional($serviceRequest->category)->name,
                    'subcat_name' => optional($serviceRequest->subCategory)->name,
                    'shop_name' => optional($serviceRequest->shop)->shop_name,
                    'shop_cat' => optional($serviceRequest->shop)->category,
                    'address' => $address,
                    'address_id' => $serviceRequest->address_id,
                    'lang' => $serviceRequest->lang,
                    'lat' => $serviceRequest->lat,
                    'desc' => $serviceRequest->desc,
                    'file' => $serviceRequest->file,
                    'file_type' => $serviceRequest->file_type,
                    'status' => $mainStatus,
                    'created_at' => $this->formatApiDateTime($serviceRequest->created_at),
                    'total_providers_bid' => count($providersList),
                    'total_shopkeepers_bid' => count($shopkeepersList),
                    'providers' => $providersList,
                    'shopkeepers' => $shopkeepersList,
                    // Add unread_counts array here
                    'unread_counts' => [
                        'user' => $totalUserUnreadCount,
                        'provider' => $totalProviderUnreadCount,
                        'shopkeeper' => $totalShopkeeperUnreadCount,
                        'total' => $totalUserUnreadCount + $totalProviderUnreadCount + $totalShopkeeperUnreadCount
                    ],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to user about booking status
     */
    private function sendNotificationToUser($userId, $title, $body, $type, $additionalData = [])
    {
        try {
            $fcmService = app(\App\Services\FcmTokenService::class);

            // Get user's FCM token
            $fcmToken = \App\Models\FCMToken::where('entity_type', 'user')
                ->where('entity_id', $userId)
                ->first();

            if (!$fcmToken || empty($fcmToken->fcm_token)) {
                Log::info("No FCM token found for user: {$userId}");
                return false;
            }

            $data = array_merge([
                'type' => $type,
                'user_id' => (string)$userId,
                'receiver_type' => 'user',
                'receiver_id' => (string)$userId,
                'timestamp' => $this->formatApiDateTime(now()),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ], $additionalData);

            $result = $fcmService->sendNotification($fcmToken->fcm_token, $title, $body, $data);

            if ($result['success']) {
                Log::info("Notification sent to user {$userId} - Type: {$type}");
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to send notification to user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to provider/shopkeeper about user action
     */
    private function sendNotificationToPartner($entityId, $entityType, $title, $body, $type, $additionalData = [])
    {
        try {
            $fcmService = app(\App\Services\FcmTokenService::class);

            // Get partner's FCM token
            $fcmToken = \App\Models\FCMToken::where('entity_type', $entityType)
                ->where('entity_id', $entityId)
                ->first();

            if (!$fcmToken || empty($fcmToken->fcm_token)) {
                Log::info("No FCM token found for {$entityType}: {$entityId}");
                return false;
            }

            $data = array_merge([
                'type' => $type,
                'entity_id' => (string)$entityId,
                'entity_type' => $entityType,
                'receiver_type' => $entityType,
                'receiver_id' => (string)$entityId,
                'timestamp' => $this->formatApiDateTime(now()),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ], $additionalData);

            $result = $fcmService->sendNotification($fcmToken->fcm_token, $title, $body, $data);

            if ($result['success']) {
                Log::info("Notification sent to {$entityType} {$entityId} - Type: {$type}");
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to send notification to {$entityType} {$entityId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new order notification to relevant partners
     */
    private function sendNewOrderNotificationToPartners($serviceRequest)
    {
        try {
            // Get subcategory details
            $subCategory = $serviceRequest->subCategory;
            $category = $serviceRequest->category;

            if (!$subCategory) {
                return false;
            }

            // Get both names
            $urduName = $subCategory->name;        // "پلمبر" (Urdu)
            $englishName = $subCategory->urdu_name; // "Plumber" (English)

            // Format: Urdu (English) e.g., "پلمبر (Plumber)"
            $displayName = $urduName . ' (' . $englishName . ')';

            $title = 'New Order Received! 🆕';
            $body = 'New service request for ' . $displayName .
                ' from ' . ($serviceRequest->user->name ?? 'Customer');

            $data = [
                'type' => 'new_order',
                'request_id' => (string)$serviceRequest->id,
                'user_id' => (string)$serviceRequest->user_id,
                'user_name' => $serviceRequest->user->name ?? 'Customer',
                'cat_id' => (string)$serviceRequest->cat_id,
                'cat_name' => $category ? $category->name : '',
                'subcat_id' => (string)$serviceRequest->subcat_id,
                'subcat_name' => $displayName,
                'subcat_urdu' => $urduName,
                'subcat_english' => $englishName,
                'desc' => $serviceRequest->desc ?? '',
                'lat' => $serviceRequest->lat ?? '',
                'lang' => $serviceRequest->lang ?? '',
                'created_at' => $this->formatApiDateTime($serviceRequest->created_at),
                'action' => 'view_order',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ];

            // Get relevant providers
            $partnerIds = $this->getRelevantPartnersForSubcategory($serviceRequest->subcat_id);

            if (empty($partnerIds['providers'])) {
                return false;
            }

            $fcmService = app(\App\Services\FcmTokenService::class);

            $sent = false;

            foreach ($partnerIds['providers'] as $providerId) {
                $fcmToken = \App\Models\FCMToken::where('entity_type', 'provider')
                    ->where('entity_id', $providerId)
                    ->first();

                if (!$fcmToken || empty($fcmToken->fcm_token)) {
                    continue;
                }

                $providerData = array_merge($data, [
                    'receiver_type' => 'provider',
                    'receiver_id' => (string)$providerId,
                    'provider_id' => (string)$providerId,
                ]);

                $fcmService->sendNotification($fcmToken->fcm_token, $title, $body, $providerData);
                $sent = true;
            }

            return $sent;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get relevant partners for a subcategory
     */
    private function getRelevantPartnersForSubcategory($subcategoryId)
    {
        $providers = [];
        $shopkeepers = [];

        if (!$subcategoryId) {
            return ['providers' => $providers, 'shopkeepers' => $shopkeepers];
        }

        // Get subcategory
        $subcategory = \App\Models\SubCategory::find($subcategoryId);

        if (!$subcategory) {
            return ['providers' => $providers, 'shopkeepers' => $shopkeepers];
        }

        // Use 'name' column (contains Urdu text)
        $searchName = $subcategory->name;

        // Search in provider services
        $allProviders = \App\Models\Provider::where('status', 'approved')->get();

        foreach ($allProviders as $provider) {
            $services = $provider->services;

            if (is_string($services)) {
                $services = json_decode($services, true);
            }

            if (!empty($services) && is_array($services)) {
                foreach ($services as $service) {
                    $subServices = $service['sub_services'] ?? [];

                    if (is_string($subServices)) {
                        $subServices = json_decode($subServices, true);
                    }

                    if (is_array($subServices) && in_array($searchName, $subServices)) {
                        $providers[] = $provider->id;
                        break;
                    }
                }
            }
        }

        $providers = array_unique($providers);

        return [
            'providers' => $providers,
            'shopkeepers' => $shopkeepers
        ];
    }

    /**
     * Send notification when order is accepted
     */
    private function sendOrderAcceptedNotification($serviceRequest)
    {
        try {
            $fcmService = app(\App\Services\FcmTokenService::class);

            $title = 'Order Accepted! ✅';
            $body = 'Your service request has been accepted by a partner';

            $data = [
                'type' => 'new_order',
                'request_id' => (string)$serviceRequest->id,
                'status' => 'accepted',
                'receiver_type' => 'user',
                'receiver_id' => (string)$serviceRequest->user_id,
                'user_id' => (string)$serviceRequest->user_id,
                'action' => 'view_order',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ];

            // Send to customer
            $fcmToken = \App\Models\FCMToken::where('entity_type', 'user')
                ->where('entity_id', $serviceRequest->user_id)
                ->first();

            if ($fcmToken && !empty($fcmToken->fcm_token)) {
                $fcmService->sendNotification($fcmToken->fcm_token, $title, $body, $data);
                Log::info("Order accepted notification sent to user: {$serviceRequest->user_id}");
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send order accepted notification: " . $e->getMessage());
            return false;
        }
    }

    private function normalizeStatus($status)
    {
        $statusMap = [
            'complete_booking' => 'complete',
            'complete' => 'complete',
            'in_progress' => 'in_progress',
            'accept' => 'accept',
            'pending' => 'pending',
            'reject' => 'reject',
        ];

        return $statusMap[$status] ?? $status;
    }
}
