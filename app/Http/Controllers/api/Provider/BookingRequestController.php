<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\ServiceRequest;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class BookingRequestController extends Controller
{

    //provider accept request
    // public function providerAcceptRequest(Request $request)
    // {
    //     $validated = $request->validate([
    //         'provider_id' => 'required|integer|exists:providers,id',
    //         'request_id'  => 'required|integer|exists:service_requests,id',
    //     ]);

    //     $hasActiveBooking = BookingRequest::where('provider_id', $validated['provider_id'])
    //         ->whereIn('status', ['pending', 'in_progress'])
    //         ->exists();

    //     if ($hasActiveBooking) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'You already have an active booking request',
    //         ], 400);
    //     }

    //     $serviceRequest = ServiceRequest::findOrFail($validated['request_id']);


    //     //  Create booking request
    //     BookingRequest::create([
    //         'provider_id'   => $validated['provider_id'],
    //         'request_id'    => $validated['request_id'],
    //         'user_id'       => $serviceRequest->user_id,
    //         'status'        => 'pending',
    //         'req_status' => 'accept',
    //         'cancel_reason' => null,
    //     ]);

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Request accepted successfully',
    //     ], 200);
    // }

    public function providerAcceptRequest(Request $request)
    {
        try {
            $validated = $request->validate([
                'provider_id' => 'required|integer|exists:providers,id',
                'request_id'  => 'required|integer|exists:service_requests,id',
            ]);

            // Check maximum 3 bookings in 'in_progress' status
            $inProgressCount = BookingRequest::where('provider_id', $validated['provider_id'])
                ->where('status', 'in_progress')
                ->count();

            $maxInProgress = 3;

            if ($inProgressCount >= $maxInProgress) {
                return response()->json([
                    'status'  => false,
                    'message' => "You cannot accept more requests. You already have {$inProgressCount} booking(s) in progress. Maximum limit is {$maxInProgress}.",
                ], 400);
            }

            // // Check if provider already has an active booking (pending or in_progress)
            // $hasActiveBooking = BookingRequest::where('provider_id', $validated['provider_id'])
            //     ->whereIn('status', ['pending', 'in_progress'])
            //     ->exists();

            // if ($hasActiveBooking) {
            //     return response()->json([
            //         'status'  => false,
            //         'message' => 'You already have an active booking request',
            //     ], 400);
            // }

            $serviceRequest = ServiceRequest::findOrFail($validated['request_id']);

            // Create booking request
            BookingRequest::create([
                'provider_id'   => $validated['provider_id'],
                'request_id'    => $validated['request_id'],
                'user_id'       => $serviceRequest->user_id,
                'status'        => 'pending',
                'req_status'    => 'accept',
                'cancel_reason' => null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Request accepted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    //provider cancel request
    public function userCancelRequest(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'request_id'  => 'required|integer|exists:service_requests,id',
        ]);

        $hasActiveBooking = ServiceRequest::where('request_id', $validated['request_id'])
            ->whereIn('status', ['complete_booking', 'in_progress'])
            ->exists();

        if ($hasActiveBooking) {
            return response()->json([
                'status'  => false,
                'message' => 'You cannot cancel the booking',
            ], 400);
        }

        $serviceRequest = ServiceRequest::findOrFail($validated['request_id']);

        if ($serviceRequest->status === 'cancel') {
            return response()->json([
                'status'  => false,
                'message' => 'This service request has already been canceled',
            ], 400);
        }

        //  Update service request status
        $serviceRequest->update([
            'status' => 'cancel',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Request canceled successfully',
        ], 200);
    }


    // customer calling for service
    public function comming(Request $request)
    {
        try {

            $validated = $request->validate([
                'provider_id'   => 'nullable|integer|exists:providers,id',
                'shopkeeper_id' => 'nullable|integer|exists:shopkeepers,id',
                'request_id' => 'required|integer|exists:booking_requests,request_id',
            ]);

            //  Must pass at least one
            if (!$request->provider_id && !$request->shopkeeper_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Provider or Shopkeeper ID is required'
                ], 422);
            }

            //  Check if ANY booking request for this request_id has assigned = 1
            $alreadyAssigned = BookingRequest::where('request_id', $validated['request_id'])
                ->where('assigned', 1)
                ->exists();

            if ($alreadyAssigned) {
                return response()->json([
                    'status' => false,
                    'message' => 'This request has already been assigned.'
                ], 400);
            }

            //  Find the specific booking request for this provider/shopkeeper
            $bookingRequest = BookingRequest::where('request_id', $validated['request_id']);

            if ($request->provider_id) {
                $bookingRequest->where('provider_id', $request->provider_id);
            }

            if ($request->shopkeeper_id) {
                $bookingRequest->where('shopkeeper_id', $request->shopkeeper_id);
            }

            $specificBooking = $bookingRequest->first();

            if (!$specificBooking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking request not found for this provider/shopkeeper'
                ], 404);
            }

            $specificBooking->update([
                'goto' => 1,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Request marked as goto successfully',
                'data'    => [
                    'booking_id' => $specificBooking->id,
                    'request_id' => $specificBooking->request_id,
                    'provider_id' => $specificBooking->provider_id,
                    'shopkeeper_id' => $specificBooking->shopkeeper_id,
                    'assigned' => $specificBooking->assigned,
                    'goto' => $specificBooking->goto
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Provider/Shopkeeper create booking
    public function goto(Request $request)
    {
        try {
            // Get the authenticated provider or shopkeeper
            $provider = Auth::guard('provider-api')->user();
            $shopkeeper = Auth::guard('shopkeeper-api')->user();

            $validated = $request->validate([
                'request_id' => 'required|integer|exists:service_requests,id', // service_request ID
            ]);

            // Ensure at least one is authenticated
            if (!$provider && !$shopkeeper) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated. Please login as provider or shopkeeper.'
                ], 401);
            }

            // Find the specific booking request for this logged-in provider/shopkeeper
            $query = BookingRequest::where('request_id', $validated['request_id']);

            if ($provider) {
                $query->where('provider_id', $provider->id);
            }

            if ($shopkeeper) {
                $query->where('shopkeeper_id', $shopkeeper->id);
            }

            $bookingRequest = $query->first();

            if (!$bookingRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking request not found for your account'
                ], 404);
            }

            // Update only this specific booking request
            $bookingRequest->update([
                'goto' => 2, // This means arrived/goto
                'assigned' => 1,
                'is_seen'  => 0,
                'seen_at'  => null,
                // 'status' => 'in_progress' // Optional: Update status to in_progress
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Request marked as arrived successfully',
                'data'    => [
                    'booking_id' => $bookingRequest->id,
                    'request_id' => $bookingRequest->request_id,
                    'goto' => 2,
                    'is_seen' => 0,
                    // 'req_status' => $bookingRequest->req_status,
                    'message' => 'User will be notified about your arrival'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function markBookingAsSeen(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|integer|exists:booking_requests,id'
            ]);

            // Get authenticated user from api guard
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized - Please login first'
                ], 401);
            }

            // Find booking
            $booking = BookingRequest::find($validated['booking_id']);

            if (!$booking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Verify that this booking belongs to the user
            if ($booking->user_id != $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not authorized to update this booking'
                ], 403);
            }

            // Update is_seen and seen_at
            $booking->update([
                'is_seen' => 1,
                'seen_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Booking marked as seen successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'is_seen' => $booking->is_seen,
                    'seen_at' => $booking->seen_at
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //shopkeeper accept request
    public function shopkeeperAcceptRequest(Request $request)
    {
        $validated = $request->validate([
            'shopkeeper_id' => 'required|integer|exists:shop_keepers,id',
            'request_id'    => 'required|integer|exists:service_requests,id',
        ]);

        // Check if shopkeeper already has a pending booking
        $hasPendingBooking = BookingRequest::where('shopkeeper_id', $validated['shopkeeper_id'])
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingBooking) {
            return response()->json([
                'status'  => false,
                'message' => 'You already have a pending booking request',
            ], 400);
        }

        // Fetch shop service request
        $shopServiceRequest = ServiceRequest::findOrFail($validated['request_id']);
        // return $shopServiceRequest;

        // // Prevent duplicate acceptance
        // if ($shopServiceRequest->status === 'accept') {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'This service request has already been accepted',
        //     ], 400);
        // }

        // // Update shop service request status
        // $shopServiceRequest->update([
        //     'status' => 'accept',
        // ]);

        // Create shop booking request
        $shopBookingRequest = BookingRequest::create([
            'shopkeeper_id' => $validated['shopkeeper_id'],
            'request_id'    => $validated['request_id'],
            'user_id'       => $shopServiceRequest->user_id,
            'status'        => 'pending',
            'req_status' => 'accept',
            'cancel_reason' => null,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Shop booking request accepted successfully',
            'data'    => $shopBookingRequest,
        ], 200);
    }


    public function userBookings()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $bookings = BookingRequest::where('user_id', $user->id)->where(['goto' => 2, 'assigned' => 1])
            ->with([
                'provider:id,full_name',
                'serviceRequest.category:id,name',
                'serviceRequest.subCategory:id,name',
                'serviceRequest.address:id,name,street,city',
                'shopkeeper:id,name',
                'serviceRequest.shop:id,shop_name,shop_image,category',
            ])->orderBy('id', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No bookings found',
                'data' => []
            ], 200);
        }

        $data = $bookings->map(function ($booking) {

            return [
                'booking_id'    => $booking->id ?? null,
                'is_seen'    => $booking->is_seen ?? null,
                'provider_name' => $booking->provider->full_name ?? null,
                'main_category' => $booking->serviceRequest->category->name ?? null,
                'sub_category'  => $booking->serviceRequest->subCategory->name ?? null,
                'shop_name'      => $booking->serviceRequest->shop->shop_name ?? null,
                'shop_cat'  => $booking->serviceRequest->shop->category ?? null,
                'shopkeeper_name' => $booking->shopkeeper->name ?? null,
                'description'   => $booking->serviceRequest->desc ?? null,

                'status'        => $booking->status,
                'price'         => $booking->price,

                'starting_date' => optional($booking->created_at)
                    ->format('Y-m-d'),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'My bookings retrieved successfully',
            'data' => $data
        ], 200);
    }


    public function myBookings()
    {
        $provider = Auth::user();

        if (!$provider) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // $bookings = BookingRequest::where('provider_id', $provider->id)->where('assigned', 1)
        $bookings = BookingRequest::where('provider_id', $provider->id)->where(['goto' => 2, 'assigned' => 1])
            ->with([
                'provider:id,full_name',
                'serviceRequest.category:id,name',
                'serviceRequest.subCategory:id,name',
                'serviceRequest.address:id,name,street,city',
                'serviceRequest.shop:id,shop_name,shop_image,category',
                'shopkeeper:id,name',
            ])->orderBy('id', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No bookings found',
                'data' => []
            ], 200);
        }

        $data = $bookings->map(function ($booking) {

            return [
                'booking_id'    => $booking->id ?? null,
                'provider_name' => $booking->provider->full_name ?? null,
                'main_category' => $booking->serviceRequest->category->name ?? null,
                'sub_category'  => $booking->serviceRequest->subCategory->name ?? null,
                'shop_name'      => $booking->serviceRequest->shop->shop_name ?? null,
                'shop_cat'  => $booking->serviceRequest->shop->category ?? null,
                'shopkeeper_name' => $booking->shopkeeper->name ?? null,
                'description'   => $booking->serviceRequest->desc ?? null,

                'status'        => $booking->status,
                'price'         => $booking->price,

                'starting_date' => optional($booking->serviceRequest->created_at)
                    ->format('Y-m-d'),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'My Booking retieved successfully',
            'data' => $data
        ], 200);
    }

    // bookingDetails for provider
    public function providerBookingDetails($id)
    {
        $provider = Auth::user();

        $booking = BookingRequest::with([
            'user:id,name,image',
            'serviceRequest.category:id,name',
            'serviceRequest.subCategory:id,name',
            'serviceRequest.address:id,name,street,city',
            'serviceRequest.shop:id,shop_name,category',
            'provider.ratings' // assuming provider hasMany ratings relationship
        ])->find($id);

        if (!$booking) {
            return response()->json([
                'status' => true,
                'message' => 'No booking found',
                'data' => []
            ], 200);
        }

        if (!$booking->is_seen && $booking->provider_id == $provider->id) {
            $booking->update([
                'is_seen' => true,
                'seen_at' => now()
            ]);
        }

        $data = [
            'booking_id'   => $booking->id,
            'request_id'    => $booking->serviceRequest->id,
            'sub_category'  => $booking->serviceRequest->subCategory->name ?? null,
            'main_category' => $booking->serviceRequest->category->name ?? null,
            'shop_name' => $booking->serviceRequest->shop->shop_name ?? null,
            'shop_cat' => $booking->serviceRequest->shop->category ?? null,
            'status'        => $booking->status,
            'details'        => $booking->details ?? null,
            'audio'        => $booking->audio ?? null,
            'cancelled_by'  => $booking->cancel_by ?? null,
            'cancel_reason'  => $booking->cancel_reason ?? null,
            'file_url'      => $booking->serviceRequest->file ?? null,
            'lang'      => $booking->serviceRequest->lang ?? null,
            'lat'      => $booking->serviceRequest->lat ?? null,
            'address'       => $booking->serviceRequest->address->name ?? null,
            'address_id'    => $booking->serviceRequest->address_id ?? null,
            'provider_name' => $booking->provider->full_name ?? null,
            'description'   => $booking->serviceRequest->desc ?? null,
            'price'         => $booking->price,
            'starting_date' => optional($booking->serviceRequest->created_at)->format('Y-m-d'),

            'user' => [
                'id'    => optional($booking->user)->id,
                'name'  => optional($booking->user)->name,
                'image' => optional($booking->user)->image
                    ? url('profiles/' . $booking->user->image)
                    : null,
            ],

        ];

        return response()->json([
            'status'  => true,
            'message' => 'Booking details retrieved successfully',
            'user_id' => $provider->id,
            'data'    => $data
        ], 200);
    }

    // bookingDetails for user
    public function bookingDetails($id)
    {
        $booking = BookingRequest::with([
            'provider:id,full_name,profile_image',
            'provider.ratings:id,provider_id,rating',

            'shopkeeper:id,name,profile_image',
            'shopkeeper.ratings:id,shopkeeper_id,rating',

            'serviceRequest.category:id,name',
            'serviceRequest.subCategory:id,name',
            'serviceRequest.address:id,name,street,city',
            'serviceRequest.shop:id,shop_name,shop_image,category',
        ])->find($id);

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'No booking found',
                'data' => []
            ], 200);
        }

        /*
    |----------------------------------------------------
    | Provider Stats
    |----------------------------------------------------
    */
        $provider = $booking->provider;

        $totalCompletedBookings = $provider
            ? $provider->bookingRequests()
            ->where('status', 'complete_booking')
            ->count()
            : 0;

        $averageRating = $provider
            ? $provider->ratings()->avg('rating')
            : 0;

        /*
    |----------------------------------------------------
    | Shopkeeper Stats
    |----------------------------------------------------
    */
        $shopkeeper = $booking->shopkeeper;

        $shopkeeperCompletedBookings = $shopkeeper
            ? $shopkeeper->bookingRequests()
            ->where('status', 'complete_booking')
            ->count()
            : 0;

        $shopkeeperAverageRating = $shopkeeper
            ? $shopkeeper->ratings()->avg('rating')
            : 0;

        /*
    |----------------------------------------------------
    | Response Data
    |----------------------------------------------------
    */
        $data = [
            'booking_id'    => $booking->id ?? null,
            'request_id'    => $booking->serviceRequest->id ?? null,
            'sub_category'  => $booking->serviceRequest->subCategory->name ?? null,
            'main_category' => $booking->serviceRequest->category->name ?? null,

            'shop_name' => $booking->serviceRequest->shop->shop_name ?? null,
            'shop_cat'  => $booking->serviceRequest->shop->category ?? null,

            'status'        => $booking->status,
            'details'        => $booking->details,
            'audio'        => $booking->audio,
            'file_url'      => $booking->serviceRequest->file ?? null,
            'address'       => $booking->serviceRequest->address->name ?? null,
            'address_id'    => $booking->serviceRequest->address_id ?? null,
            'description'   => $booking->serviceRequest->desc ?? null,
            'lang'          => $booking->serviceRequest->lang ?? null,
            'lat'           => $booking->serviceRequest->lat ?? null,
            'cancelled_by'  => $booking->cancel_by ?? null,
            'cancel_reason' => $booking->cancel_reason ?? null,
            'price'         => $booking->price,
            'starting_date' => optional($booking->created_at)->format('Y-m-d'),

            'provider' => $provider ? [
                'id' => $provider->id,
                'name' => $provider->full_name,
                'profile_image' => $provider->profile_image
                    ? url($provider->profile_image)
                    : null,
                'total_completed_bookings' => $totalCompletedBookings,
                'average_rating' => round($averageRating, 1),
            ] : null,

            'shopkeeper' => $shopkeeper ? [
                'id' => $shopkeeper->id,
                'goto'        => $booking->goto,
                'name' => $shopkeeper->name,
                'profile_image' => $shopkeeper->profile_image
                    ?? null,
                'total_completed_bookings' => $shopkeeperCompletedBookings,
                'average_rating' => round($shopkeeperAverageRating, 1),
            ] : null,
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Booking details retrieved successfully',
            'data'    => $data
        ], 200);
    }


    // startBooking/ cancelBooking
    // public function startBooking(Request $request)
    // {
    //     $validated = $request->validate([
    //         'booking_id'     => 'required|exists:booking_requests,id',
    //         'status'         => 'required|string|in:in_progress,cancel',
    //         'amount'         => 'nullable|integer',
    //         'cancel_reason'  => 'nullable|string',
    //         'details'        => 'nullable|string',
    //         'audio'          => 'nullable',
    //         // 'audio'          => 'nullable|file|mimes:mp3,wav,m4a,ogg,aac',
    //     ]);

    //     $booking = BookingRequest::findOrFail($validated['booking_id']);
    //     $authUser = Auth::user();

    //     // 🔒 Block updates if booking already finished
    //     if (in_array($booking->status, ['completed', 'cancel'])) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'This booking can no longer be updated'
    //         ], 400);
    //     }

    //     /**
    //      * ===============================
    //      * STORE DETAILS
    //      * ===============================
    //      */
    //     if ($request->filled('details')) {
    //         $booking->details = $validated['details'];
    //     }

    //     /**
    //      * ===============================
    //      * STORE AUDIO FILE
    //      * ===============================
    //      */
    //     if ($request->hasFile('audio')) {
    //         $file = $request->file('audio');
    //         $directory = public_path('booking_audio');

    //         // Create directory if it doesn't exist
    //         if (!file_exists($directory)) {
    //             mkdir($directory, 0755, true);
    //         }

    //         $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
    //         $file->move($directory, $fileName);
    //         $booking->audio = 'booking_audio/' . $fileName;
    //     }

    //     /**
    //      * ===============================
    //      * PROVIDER ACTIONS
    //      * ===============================
    //      */
    //     if ($authUser->id === $booking->provider_id) {

    //         if ($booking->status === 'in_progress' && $validated['status'] === 'in_progress') {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Booking is already in progress'
    //             ], 400);
    //         }

    //         $booking->status = $validated['status'];
    //         $booking->price  = $validated['amount'] ?? $booking->price;

    //         if ($validated['status'] === 'cancel') {
    //             $booking->cancel_by = 'provider';
    //             $booking->cancel_reason = $validated['cancel_reason'];
    //         }
    //     }

    //     /**
    //      * ===============================
    //      * USER (CUSTOMER) ACTIONS
    //      * ===============================
    //      */
    //     elseif ($authUser->id === $booking->user_id) {

    //         if ($validated['status'] !== 'cancel') {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'User can only cancel the booking'
    //             ], 403);
    //         }

    //         $booking->status = 'cancel';
    //         $booking->cancel_by = 'user';
    //         $booking->cancel_reason = $validated['cancel_reason'];
    //     }

    //     /**
    //      * ===============================
    //      * UNAUTHORIZED
    //      * ===============================
    //      */
    //     else {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Unauthorized to update this booking'
    //         ], 403);
    //     }

    //     $booking->save();

    //     return response()->json([
    //         'status'  => true,
    //         'message' => $booking->status === 'cancel'
    //             ? 'Booking cancelled successfully'
    //             : 'Booking started successfully',
    //         'data' => [
    //             'booking_id'    => $booking->id,
    //             'status'        => $booking->status,
    //             'details'       => $booking->details,
    //             'audio'         => $booking->audio,
    //             'cancel_by'     => $booking->cancel_by,
    //             'cancel_reason' => $booking->cancel_reason,
    //         ]
    //     ], 200);
    // }

    public function startBooking(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id'     => 'required|exists:booking_requests,id',
                'status'         => 'required|string|in:in_progress,cancel',
                'amount'         => 'nullable|integer',
                'cancel_reason'  => 'nullable|string',
                'details'        => 'nullable|string',
                'audio'          => 'nullable|file|mimes:mp3,wav,m4a,ogg,aac|max:102400',
            ]);

            $booking = BookingRequest::findOrFail($validated['booking_id']);
            $authUser = Auth::user();

            //  Block updates if booking already finished
            if (in_array($booking->status, ['completed', 'cancel'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'This booking can no longer be updated'
                ], 400);
            }

            if ($request->filled('details')) {
                $booking->details = $validated['details'];
            }

            if ($request->hasFile('audio')) {
                $file = $request->file('audio');

                // Same as upload function
                $fileType = $file->getClientOriginalExtension();
                $fileName = time() . '_' . $file->getClientOriginalName();

                $uploadDir = public_path('uploads');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Move the file
                $file->move($uploadDir, $fileName);

                // Store relative path (same as upload function)
                $booking->audio = 'uploads/' . $fileName;
            }

            if ($authUser->id === $booking->provider_id) {

                if ($booking->status === 'in_progress' && $validated['status'] === 'in_progress') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Booking is already in progress'
                    ], 400);
                }

                $booking->status = $validated['status'];
                $booking->price  = $validated['amount'] ?? $booking->price;

                if ($validated['status'] === 'cancel') {
                    $booking->cancel_by = 'provider';
                    $booking->cancel_reason = $validated['cancel_reason'];
                }
            }
            elseif ($authUser->id === $booking->user_id) {

                if ($validated['status'] !== 'cancel') {
                    return response()->json([
                        'status' => false,
                        'message' => 'User can only cancel the booking'
                    ], 403);
                }

                $booking->status = 'cancel';
                $booking->cancel_by = 'user';
                $booking->cancel_reason = $validated['cancel_reason'];
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to update this booking'
                ], 403);
            }

            $booking->save();

            return response()->json([
                'status'  => true,
                'message' => $booking->status === 'cancel'
                    ? 'Booking cancelled successfully'
                    : 'Booking started successfully',
                'data' => [
                    'booking_id'    => $booking->id,
                    'status'        => $booking->status,
                    'details'       => $booking->details,
                    // 'audio_url'     => $booking->audio ? url($booking->audio) : null,
                    'audio_path'    => $booking->audio,
                    'cancel_by'     => $booking->cancel_by,
                    'cancel_reason' => $booking->cancel_reason,
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    // bookingComplete
    public function completeBookingStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id'    => 'required|integer|exists:booking_requests,id',
                'status'        => 'required|string|in:complete_booking',
                'payment_type'  => 'required|string|in:cash',
            ]);

            $booking = BookingRequest::find($validated['booking_id']);
            $booking->status = $validated['status'];
            $booking->payment_type = $validated['payment_type'];
            $booking->save();

            // Prepare response data
            $responseData = [
                'booking_id' => $booking->id,
                'payment_type' => $booking->payment_type,
                'status'     => $booking->status,
            ];
            return response()->json([
                'status'  => true,
                'message' => 'Booking status updated successfully',
                'data'    => $responseData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => $e->getMessage(),
            ]);
        }
    }


    // display requests to providers
    // public function allRequests()
    // {
    //     try {
    //         $provider = Auth::guard('provider-api')->user();
    //         $shopkeeper = Auth::guard('shopkeeper-api')->user();

    //         $query = ServiceRequest::with([
    //             'category:id,name',
    //             'subCategory:id,name',
    //             'bookingRequests' => function ($q) use ($provider, $shopkeeper) {
    //                 // Only load booking requests related to this provider/shopkeeper
    //                 if ($provider) {
    //                     $q->where('provider_id', $provider->id);
    //                 }
    //                 if ($shopkeeper) {
    //                     $q->where('shopkeeper_id', $shopkeeper->id);
    //                 }
    //             },
    //             'bookingRequests.provider:id,full_name',
    //             'shop:id,shop_name,category,shopkeeper_id',
    //             'bookingRequests.shopkeeper:id,name'
    //         ])->whereIn('status', ['pending', 'accept'])->orderBy('id', 'desc');

    //         // Provider Filter
    //         if ($provider) {
    //             $subCategoryNames = collect($provider->services)
    //                 ->pluck('sub_services')
    //                 ->flatten()
    //                 ->filter()
    //                 ->toArray();

    //             $subCategoryIds = SubCategory::whereIn('name', $subCategoryNames)
    //                 ->pluck('id')
    //                 ->toArray();

    //             if (!empty($subCategoryIds)) {
    //                 $query->whereIn('subcat_id', $subCategoryIds);
    //             }
    //         }

    //         // Shopkeeper Filter
    //         if ($shopkeeper) {
    //             $query->whereHas('shop', function ($q) use ($shopkeeper) {
    //                 $q->where('shopkeeper_id', $shopkeeper->id);
    //             });
    //         }

    //         $serviceRequests = $query->get()->map(function ($request) use ($provider, $shopkeeper) {

    //             // Find the specific booking request for this provider/shopkeeper
    //             $specificBooking = null;

    //             if ($provider) {
    //                 $specificBooking = $request->bookingRequests
    //                     ->where('provider_id', $provider->id)
    //                     ->first();
    //             }

    //             if ($shopkeeper) {
    //                 $specificBooking = $request->bookingRequests
    //                     ->where('shopkeeper_id', $shopkeeper->id)
    //                     ->first();
    //             }

    //             // Determine the correct status
    //             $requestStatus = 'pending'; // Default status

    //             if ($specificBooking) {
    //                 // If booking exists, use its req_status
    //                 $requestStatus = $specificBooking->req_status;
    //             } else {
    //                 // No booking yet - check if provider/shopkeeper is eligible
    //                 // and show 'pending' or 'new' status
    //                 $requestStatus = 'pending';
    //             }

    //             // Get provider/shopkeeper name from the specific booking
    //             $providerName = null;
    //             $shopkeeperName = null;

    //             if ($specificBooking && $specificBooking->provider) {
    //                 $providerName = $specificBooking->provider->full_name;
    //             }

    //             if ($specificBooking && $specificBooking->shopkeeper) {
    //                 $shopkeeperName = $specificBooking->shopkeeper->name;
    //             }

    //             return [
    //                 'id' => $request->id,
    //                 'is_seen' => $request->is_seen,
    //                 'cat_name' => optional($request->category)->name,
    //                 'subcat_name' => optional($request->subCategory)->name,
    //                 'shop_name' => optional($request->shop)->shop_name,
    //                 'shop_cat' => optional($request->shop)->category,
    //                 'desc' => $request->desc,
    //                 'status' => $requestStatus, // Now shows 'pending' for fresh providers
    //                 'created_at' => $request->created_at,
    //                 'provider_name' => $providerName,
    //                 'shopkeeper_name' => $shopkeeperName,
    //                 'has_accepted' => $specificBooking ? true : false, // Optional: to know if they've accepted
    //                 'booking_id' => $specificBooking ? $specificBooking->id : null, // Optional: booking ID if exists
    //             ];
    //         });

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Requests fetched successfully',
    //             'data' => $serviceRequests
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function allRequests()
    {
        try {
            $provider = Auth::guard('provider-api')->user();
            $shopkeeper = Auth::guard('shopkeeper-api')->user();

            $query = ServiceRequest::with([
                'category:id,name',
                'subCategory:id,name',
                'bookingRequests' => function ($q) use ($provider, $shopkeeper) {
                    if ($provider) {
                        $q->where('provider_id', $provider->id);
                    }
                    if ($shopkeeper) {
                        $q->where('shopkeeper_id', $shopkeeper->id);
                    }
                },
                'bookingRequests.provider:id,full_name',
                'shop:id,shop_name,category,shopkeeper_id',
                'bookingRequests.shopkeeper:id,name'
            ])->whereIn('status', ['pending', 'accept'])->orderBy('id', 'desc');

            // Provider Filter
            if ($provider) {
                $subCategoryNames = collect($provider->services)
                    ->pluck('sub_services')
                    ->flatten()
                    ->filter()
                    ->toArray();

                $subCategoryIds = SubCategory::whereIn('name', $subCategoryNames)
                    ->pluck('id')
                    ->toArray();

                if (!empty($subCategoryIds)) {
                    $query->whereIn('subcat_id', $subCategoryIds);
                }
            }

            // Shopkeeper Filter
            if ($shopkeeper) {
                $query->whereHas('shop', function ($q) use ($shopkeeper) {
                    $q->where('shopkeeper_id', $shopkeeper->id);
                });
            }

            $serviceRequests = $query->get()->map(function ($request) use ($provider, $shopkeeper) {

                $booking = null;

                if ($provider) {
                    $booking = $request->bookingRequests->firstWhere('provider_id', $provider->id);
                } elseif ($shopkeeper) {
                    $booking = $request->bookingRequests->firstWhere('shopkeeper_id', $shopkeeper->id);
                }

                return [
                    'id' => $request->id,
                    'is_seen' => $request->is_seen,
                    'cat_name' => optional($request->category)->name,
                    'subcat_name' => optional($request->subCategory)->name,
                    'shop_name' => optional($request->shop)->shop_name,
                    'shop_cat' => optional($request->shop)->category,
                    'desc' => $request->desc,
                    'status' => $booking ? $booking->req_status : 'pending', // Fixed!
                    'created_at' => $request->created_at,
                    'provider_name' => optional(optional($booking)->provider)->full_name,
                    'shopkeeper_name' => optional(optional($booking)->shopkeeper)->name,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Requests fetched successfully',
                'data' => $serviceRequests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // mark as seen
    public function markRequestAsSeen(Request $request)
    {
        try {
            $validated = $request->validate([
                'request_id' => 'required|integer|exists:service_requests,id'
            ]);

            // Check which user is authenticated
            $provider = Auth::guard('provider-api')->user();
            $shopkeeper = Auth::guard('shopkeeper-api')->user();

            $authenticatedUser = null;
            $userType = null;
            $userId = null;

            if ($provider) {
                $authenticatedUser = $provider;
                $userType = 'provider';
                $userId = $provider->id;
            } elseif ($shopkeeper) {
                $authenticatedUser = $shopkeeper;
                $userType = 'shopkeeper';
                $userId = $shopkeeper->id;
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized - Please login as provider or shopkeeper'
                ], 401);
            }

            // Find booking request
            $bookingRequest = ServiceRequest::where('id', $validated['request_id'])
                ->first();

            if (!$bookingRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking not found for this request'
                ], 404);
            }

            // Verify that this booking belongs to the authenticated user
            $authorized = false;

            if ($userType == 'provider' && $bookingRequest->provider_id == $userId) {
                $authorized = true;
            } elseif ($userType == 'shopkeeper' && $bookingRequest->shopkeeper_id == $userId) {
                $authorized = true;
            }

            if (!$authorized) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not authorized to update this request'
                ], 403);
            }

            // Update is_seen and seen_at
            $bookingRequest->update([
                'is_seen' => 1,
                'seen_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => ucfirst($userType) . ' request marked as seen successfully',
                'data' => [
                    'request_id' => $validated['request_id'],
                    'booking_id' => $bookingRequest->id,
                    'is_seen' => $bookingRequest->is_seen,
                    'seen_at' => $bookingRequest->seen_at,
                    'seen_by' => $userType
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // request details for provider
    public function providerRequestDetails($id)
    {
        try {
            $provider = Auth::guard('provider-api')->user();

            $serviceRequest = ServiceRequest::with([
                'user:id,name,image',
                'category:id,name',
                'subCategory:id,name',
                'address:id,name,street,city,PostalCode',
                'bookingRequests' => function ($q) use ($provider) {
                    // Only load booking requests for this specific provider
                    if ($provider) {
                        $q->where('provider_id', $provider->id);
                    }
                },
                'bookingRequests.provider.ratings',
                'shop:id,shop_name,category'
            ])
                ->find($id);

            if (!$serviceRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service request not found'
                ], 404);
            }

            /*
        |--------------------------------------------------------------------------
        | Address
        |--------------------------------------------------------------------------
        */
            $address = null;
            if ($serviceRequest->address) {
                $address = collect([
                    $serviceRequest->address->name,
                    $serviceRequest->address->street,
                    $serviceRequest->address->city,
                    $serviceRequest->address->PostalCode,
                ])->filter()->implode(', ');
            }

            /*
        |--------------------------------------------------------------------------
        | Find the booking request for this specific provider
        |--------------------------------------------------------------------------
        */
            $specificBooking = null;
            if ($provider) {
                $specificBooking = $serviceRequest->bookingRequests
                    ->where('provider_id', $provider->id)
                    ->first();
            }

            /*
        |--------------------------------------------------------------------------
        | Provider Data
        |--------------------------------------------------------------------------
        */
            $providerData = null;
            if ($specificBooking && $specificBooking->provider) {
                $provider = $specificBooking->provider;

                $totalCompletedBookings = $provider->bookingRequests()
                    ->where('req_status', 'complete_booking')
                    ->count();

                $averageRating = $provider->ratings()->avg('rating');

                $providerData = [
                    'id' => $provider->id,
                    'name' => $provider->full_name,
                    'profile_image' => $provider->profile_image
                        ? url('profiles/' . $provider->profile_image)
                        : null,
                    'total_completed_bookings' => $totalCompletedBookings,
                    'average_rating' => $averageRating ? round($averageRating, 1) : 0,
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Service request details retrieved successfully',
                'data' => [
                    'id' => $serviceRequest->id,
                    'user_id' => $serviceRequest->user_id,
                    'goto' => $specificBooking ? ($specificBooking->goto ?? 0) : 0,
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
                    'status' => $specificBooking ? $specificBooking->req_status : 'pending', // Fixed!
                    'created_at' => $serviceRequest->created_at,
                    'user' => [
                        'id'    => optional($serviceRequest->user)->id,
                        'name'  => optional($serviceRequest->user)->name,
                        'image' => optional($serviceRequest->user)->image
                            ? url('profiles/' . $serviceRequest->user->image)
                            : null,
                    ],
                    'provider' => $providerData, // Only show provider data if they've accepted
                    'booking_id' => $specificBooking ? $specificBooking->id : null, // Optional
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
}
