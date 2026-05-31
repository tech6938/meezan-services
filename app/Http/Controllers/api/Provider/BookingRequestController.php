<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\Commission;
use App\Models\ProviderRequestSeen;
use App\Models\ServiceRequest;
use App\Models\SubCategory;
use App\Models\Wallet;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class BookingRequestController extends Controller
{

    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    //provider accept request
    // public function providerAcceptRequest(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'provider_id' => 'required|integer|exists:providers,id',
    //             'request_id'  => 'required|integer|exists:service_requests,id',
    //         ]);

    //         // Check wallet balance for the provider before accepting request
    //         $wallet = Wallet::where('provider_id', $validated['provider_id'])->first();
    //         $walletBalance = $wallet ? (float) $wallet->amount : 0.00;

    //         if ($walletBalance < 0) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'Please add your wallet balance!',
    //             ], 400);
    //         }

    //         // Check maximum 3 bookings in 'in_progress' status
    //         $inProgressCount = BookingRequest::where('provider_id', $validated['provider_id'])
    //             ->where('status', 'in_progress')
    //             ->count();

    //         $maxInProgress = 3;

    //         if ($inProgressCount >= $maxInProgress) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => "You cannot accept more requests. You already have {$inProgressCount} booking(s) in progress. Maximum limit is {$maxInProgress}.",
    //             ], 400);
    //         }


    //         $orderNo = now()->format('YmdHis') . random_int(10, 99);

    //         $serviceRequest = ServiceRequest::findOrFail($validated['request_id']);

    //         // Create booking request
    //         BookingRequest::create([
    //             'provider_id'   => $validated['provider_id'],
    //             'request_id'    => $validated['request_id'],
    //             'user_id'       => $serviceRequest->user_id,
    //             'status'        => 'pending',
    //             'req_status'    => 'accept',
    //             'order_no'    => $orderNo,
    //             'cancel_reason' => null,
    //         ]);

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Request accepted successfully',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Something went wrong',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function providerAcceptRequest(Request $request)
    {
        try {
            $validated = $request->validate([
                'provider_id' => 'required|integer|exists:providers,id',
                'request_id'  => 'required|integer|exists:service_requests,id',
            ]);

            $providerId = $validated['provider_id'];
            $requestId = $validated['request_id'];

            // Check 1: Negative balance
            $wallet = Wallet::where('provider_id', $providerId)->first();
            $walletBalance = $wallet ? (float) $wallet->amount : 0.00;

            if ($walletBalance < 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot accept bookings because your balance is negative.'
                ], 400);
            }

            // Check 2: Maximum 3 in-progress bookings
            $inProgressCount = BookingRequest::where('provider_id', $providerId)
                ->where('status', 'in_progress')
                ->count();

            if ($inProgressCount >= 3) {
                return response()->json([
                    'status' => false,
                    'message' => 'You already have 3 bookings in progress.'
                ], 400);
            }

            // Check 3: Pending bookings
            $hasPendingBookings = BookingRequest::where('provider_id', $providerId)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingBookings) {
                return response()->json([
                    'status' => false,
                    'message' => 'You have pending bookings that must be completed first.'
                ], 400);
            }

            // All checks passed - create booking
            $orderNo = now()->format('YmdHis') . random_int(10, 99);
            $serviceRequest = ServiceRequest::findOrFail($requestId);

            $booking = BookingRequest::create([
                'provider_id'   => $providerId,
                'request_id'    => $requestId,
                'user_id'       => $serviceRequest->user_id,
                'status'        => 'pending',
                'req_status'    => 'accept',
                'order_no'      => $orderNo,
                'cancel_reason' => null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Request accepted successfully',
                'data'    => [
                    'booking_id' => $booking->id,
                    'order_no'   => $orderNo
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid data provided'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again.'
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



        $provider = $booking->provider;

        $totalCompletedBookings = $provider
            ? $provider->bookingRequests()
            ->where('status', 'complete_booking')
            ->count()
            : 0;

        $averageRating = $provider
            ? $provider->ratings()->avg('rating')
            : 0;



        $shopkeeper = $booking->shopkeeper;

        $shopkeeperCompletedBookings = $shopkeeper
            ? $shopkeeper->bookingRequests()
            ->where('status', 'complete_booking')
            ->count()
            : 0;

        $shopkeeperAverageRating = $shopkeeper
            ? $shopkeeper->ratings()->avg('rating')
            : 0;



        $data = [
            'booking_id'    => $booking->id ?? null,
            'request_id'    => $booking->serviceRequest->id ?? null,
            'is_seen'       => $booking->is_seen ?? 0,
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
    public function startBooking(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id'     => 'required|exists:booking_requests,id',
                'status'         => 'required|string|in:in_progress,cancel',
                'amount'         => 'nullable|integer',
                'cancel_reason'  => 'nullable|string',
                'details'        => 'nullable|string',
                'audio'          => $this->audioUploadRule(),
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
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());

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
            } elseif ($authUser->id === $booking->user_id) {

                if ($validated['status'] !== 'cancel') {
                    return response()->json([
                        'status' => false,
                        'message' => 'User can only cancel the booking'
                    ], 403);
                }

                $booking->status = 'cancel';
                $booking->cancel_by = 'user';
                $booking->cancel_reason = $validated['cancel_reason'];
            } else {
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
                    'audio'         => $booking->audio,
                    'audio_path'    => $booking->getRawOriginal('audio'),
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
                'price'         => 'required|numeric|min:0.01',
                'details'       => 'nullable|string',
                'audio'         => $this->audioUploadRule(),
            ]);

            DB::beginTransaction();

            $booking = BookingRequest::find($validated['booking_id']);

            // Check if booking is already completed
            if ($booking->status === 'complete_booking') {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking is already completed.'
                ], 400);
            }

            if ($request->filled('details')) {
                $booking->details = $validated['details'];
            }

            if ($request->hasFile('audio')) {
                $file = $request->file('audio');
                $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());

                $uploadDir = public_path('uploads');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $file->move($uploadDir, $fileName);
                $booking->audio = 'uploads/' . $fileName;
            }

            // Update booking status
            $booking->status = $validated['status'];
            $booking->req_status = $validated['status'];
            $booking->payment_type = $validated['payment_type'];
            $booking->price = $validated['price'];
            $booking->save();

            // Process commission deduction
            $commissionResult = $this->commissionService->processCommissionDeduction($booking);

            if (!$commissionResult['success']) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Commission deduction failed: ' . $commissionResult['message'],
                    'data' => [
                        'booking_id' => $booking->id,
                        'status' => $booking->status
                    ]
                ], 500);
            }

            DB::commit();

            // Prepare response data
            $responseData = [
                'booking_id' => $booking->id,
                'price' => $booking->price,
                'status' => $booking->status,
                'req_status' => $booking->req_status,
                'details' => $booking->details,
                'audio' => $booking->audio,
                'audio_path' => $booking->getRawOriginal('audio'),
                'payment_type' => $booking->payment_type,
                'commission_deducted' => $commissionResult['commission_deducted'] ?? 0,
                'wallet_balance' => $commissionResult['new_balance'],
                'commission_message' => $commissionResult['message'] ?? null,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Booking completed and commission deducted successfully',
                'data' => $responseData
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking completion failed: ' . $e->getMessage(), [
                'booking_id' => $request->booking_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while completing the booking: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optional: Get commission details for a booking (for preview before completion)
     */
    public function getCommissionDetails(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|integer|exists:booking_requests,id'
            ]);

            $booking = BookingRequest::find($validated['booking_id']);
            $serviceRequest = $booking->serviceRequest;

            if (!$serviceRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service request not found'
                ], 404);
            }

            $commission = Commission::where('sub_category_id', $serviceRequest->subcat_id)->first();

            if (!$commission) {
                return response()->json([
                    'status' => true,
                    'message' => 'No commission defined for this service',
                    'data' => [
                        'has_commission' => false,
                        'commission_amount' => 0
                    ]
                ]);
            }

            $commissionAmount = $commission->type === 'percentage'
                ? ($commission->amount / 100) * $booking->price
                : $commission->amount;

            return response()->json([
                'status' => true,
                'data' => [
                    'has_commission' => true,
                    'commission_type' => $commission->type,
                    'commission_rate' => $commission->amount,
                    'booking_price' => $booking->price,
                    'commission_amount' => $commissionAmount,
                    'wallet_balance_after' => $this->getWalletBalance($booking) - $commissionAmount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getWalletBalance($booking)
    {
        if ($booking->provider_id) {
            $wallet = Wallet::where('provider_id', $booking->provider_id)->first();
        } elseif ($booking->shopkeeper_id) {
            $wallet = Wallet::where('shopkeeper_id', $booking->shopkeeper_id)->first();
        } else {
            return 0;
        }

        return $wallet ? $wallet->amount : 0;
    }

    private function audioUploadRule(): array
    {
        $allowedExtensions = ['mp3', 'wav', 'm4a', 'aac', 'ogg'];
        $allowedMimeTypes = [
            'audio/mpeg',
            'audio/mp3',
            'audio/wav',
            'audio/x-wav',
            'audio/wave',
            'audio/vnd.wave',
            'audio/mp4',
            'audio/x-m4a',
            'audio/aac',
            'audio/x-aac',
            'audio/ogg',
            'application/ogg',
            'application/octet-stream',
        ];

        return [
            'nullable',
            'file',
            'max:102400',
            function ($attribute, $value, $fail) use ($allowedExtensions, $allowedMimeTypes) {
                if (!$value) {
                    return;
                }

                $extension = strtolower($value->getClientOriginalExtension() ?: $value->extension() ?: '');
                $mimeType = strtolower((string) $value->getMimeType());

                $hasValidExtension = in_array($extension, $allowedExtensions, true);
                $hasValidMimeType = in_array($mimeType, $allowedMimeTypes, true) || str_starts_with($mimeType, 'audio/');

                if (!$hasValidExtension && !$hasValidMimeType) {
                    $fail('The ' . $attribute . ' field must be an audio file of type: mp3, wav, m4a, aac, ogg.');
                }
            },
        ];
    }


    // display requests to providers
    public function allRequests()
    {
        try {
            $provider = Auth::guard('provider-api')->user();
            $shopkeeper = Auth::guard('shopkeeper-api')->user();

            $query = ServiceRequest::with([
                'category:id,name',
                'subCategory:id,name',
                'providerSeens' => function ($q) use ($provider) {
                    if ($provider) {
                        $q->where('provider_id', $provider->id);
                    }
                },
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
            ])->whereIn('status', ['pending', 'accept', 'cancel'])->orderBy('id', 'desc');

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
                    // return $booking;
                } elseif ($shopkeeper) {
                    $booking = $request->bookingRequests->firstWhere('shopkeeper_id', $shopkeeper->id);
                }

                $providerSeen = $provider
                    ? $request->providerSeens->firstWhere('provider_id', $provider->id)
                    : null;

                return [
                    'id' => $request->id,
                    'is_seen' => $providerSeen ? (int) $providerSeen->is_seen : 0,
                    'cat_name' => optional($request->category)->name,
                    'subcat_name' => optional($request->subCategory)->name,
                    'shop_name' => optional($request->shop)->shop_name,
                    'shop_cat' => optional($request->shop)->category,
                    'desc' => $request->desc,
                    'status' => $booking ? $booking->req_status : $request->status, // Fixed: Now uses ServiceRequest status when no booking
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

            $provider = Auth::guard('provider-api')->user();

            if (!$provider) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized - Please login as provider'
                ], 401);
            }

            $serviceRequest = ServiceRequest::find($validated['request_id']);

            if (!$serviceRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Request not found'
                ], 404);
            }

            $providerSeen = ProviderRequestSeen::updateOrCreate([
                'request_id' => $serviceRequest->id,
                'provider_id' => $provider->id,
            ], [
                'is_seen' => 1,
                'seen_at' => now(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Request marked as seen successfully',
                'data' => [
                    'request_id' => $serviceRequest->id,
                    'provider_id' => $provider->id,
                    'is_seen' => (int) $providerSeen->is_seen,
                    'seen_at' => $providerSeen->seen_at,
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
                'providerSeens' => function ($q) use ($provider) {
                    if ($provider) {
                        $q->where('provider_id', $provider->id);
                    }
                },
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

            $address = null;
            if ($serviceRequest->address) {
                $address = collect([
                    $serviceRequest->address->name,
                    $serviceRequest->address->street,
                    $serviceRequest->address->city,
                    $serviceRequest->address->PostalCode,
                ])->filter()->implode(', ');
            }

            $specificBooking = null;
            if ($provider) {
                $specificBooking = $serviceRequest->bookingRequests
                    ->where('provider_id', $provider->id)
                    ->first();
            }

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

            // Apply the same logic: if booking exists -> use booking status, else use service request status
            $status = $specificBooking ? $specificBooking->req_status : $serviceRequest->status;
            $providerSeen = $provider
                ? $serviceRequest->providerSeens->firstWhere('provider_id', $provider->id)
                : null;

            return response()->json([
                'status' => true,
                'message' => 'Service request details retrieved successfully',
                'data' => [
                    'id' => $serviceRequest->id,
                    'user_id' => $serviceRequest->user_id,
                    'is_seen' => $providerSeen ? (int) $providerSeen->is_seen : 0,
                    'goto' => $specificBooking ? ($specificBooking->goto ?? 0) : 0,
                    'assigned' => $specificBooking ? ($specificBooking->assigned ?? 0) : 0,
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
                    'status' => $status, // Changed: now uses service request status if no booking exists
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
