<?php

namespace App\Http\Controllers\api\Shop;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\ShopServiceRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingRequest as ShopBookingRequest;

class ShopBookingRequestController extends Controller
{
    /**
     * Shopkeeper accepts a shop service request
     */
    public function acceptRequest(Request $request)
    {
        $validated = $request->validate([
            'shopkeeper_id' => 'required|integer|exists:shop_keepers,id',
            'request_id'    => 'required|integer|exists:service_requests,id',
        ]);

        // Check if shopkeeper already has a pending booking
        $hasPendingBooking = ShopBookingRequest::where('shopkeeper_id', $validated['shopkeeper_id'])
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

        // Prevent duplicate acceptance
        if ($shopServiceRequest->status === 'accept') {
            return response()->json([
                'status'  => false,
                'message' => 'This service request has already been accepted',
            ], 400);
        }

        // Update shop service request status
        $shopServiceRequest->update([
            'status' => 'accept',
        ]);

        // Create shop booking request
        $shopBookingRequest = ShopBookingRequest::create([
            'shopkeeper_id' => $validated['shopkeeper_id'],
            'request_id'    => $validated['request_id'],
            'user_id'       => $shopServiceRequest->user_id,
            'status'        => 'pending',
            'cancel_reason' => null,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Shop booking request accepted successfully',
            'data'    => $shopBookingRequest,
        ], 200);
    }

    /**
     * Get all shop service requests for shopkeeper
     */
    public function allRequests()
    {
        try {
            $requests = ShopServiceRequest::with([
                'user:id,name',
                'category:id,name',
                'shop:id,shop_name,shop_image',
                'address:id,name,street,city'
            ])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($requests->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'No shop service requests found',
                    'data' => []
                ], 200);
            }

            $data = $requests->map(function ($request) {
                return [
                    'id'         => $request->id,
                    'cat_name'   => optional($request->category)->name,
                    'shop_name'  => optional($request->shop)->shop_name,
                    // 'shop_image' => optional($request->shop)->shop_image
                    //     ? url('shops/' . $request->shop->shop_image)
                    //     : null,
                    'desc'       => $request->desc,
                    'status'     => $request->status,
                    'user_name'  => $request->user->name,
                    'created_at' => $this->formatApiDateTime(optional($request->created_at)),
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Shop service requests retrieved successfully',
                'data' => $data
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
     * Get all bookings for shopkeeper
     */
    public function requestDetails($id)
    {
        try {
            $request = ShopServiceRequest::with([
                'category:id,name',
                'shop:id,shop_name,shop_image,lang,lat',
                'address:id,name,street,city,PostalCode',
                'user:id,name,image',
                'shopBookingRequest'
            ])->find($id);

            if (!$request) {
                return response()->json([
                    'status' => false,
                    'message' => 'Shop service request not found'
                ], 404);
            }

            // Build address
            $address = null;
            if ($request->address) {
                $address = collect([
                    $request->address->name,
                    $request->address->street,
                    $request->address->city,
                    $request->address->PostalCode,
                ])->filter()->implode(', ');
            }

            $data = [
                'id'         => $request->id,
                'user_id'    => $request->user_id,
                'cat_name'   => optional($request->category)->name,
                'shop_name'  => optional($request->shop)->shop_name,
                'shop_image' => optional($request->shop)->shop_image
                    ? url('shops/' . $request->shop->shop_image)
                    : null,
                'address'    => $address,
                'address_id' => $request->address_id,
                'lang'       => $request->lang,
                'lat'        => $request->lat,
                'desc'       => $request->desc,
                'file'       => $request->file,
                'file_type'  => $request->file_type,
                'status'     => $request->status,
                'created_at' => $this->formatApiDateTime(optional($request->created_at)),
                'user'       => [
                    'name'  => optional($request->user)->name,
                    'image' => optional($request->user)->image
                        ? url('users/' . $request->user->image)
                        : null,
                ],
            ];

            return response()->json([
                'status' => true,
                'message' => 'Shop service request details retrieved successfully',
                'data' => $data
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
     * Get all bookings for shopkeeper
     */
    public function myBookings()
    {
        $user = Auth::user();
        $bookings = ShopBookingRequest::where('user_id', $user->id)
        ->with([
            'shopkeeper:id,name',
            'shopServiceRequest.shop:id,shop_name,shop_image',
            'shopServiceRequest.category:id,name',
            'shopServiceRequest.address:id,name,street,city',
            ])
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
                'booking_id'     => $booking->id ?? null,
                'shop_name'      => $booking->shopServiceRequest->shop->shop_name ?? null,
                'category'       => $booking->shopServiceRequest->category->name ?? null,
                'description'    => $booking->shopServiceRequest->desc ?? null,
                'status'         => $booking->status,
                'price'          => $booking->price,
                'starting_date'  => $this->formatApiDateTime(optional($booking->created_at)),
                'shopkeeper_name' => $booking->shopkeeper->name ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Shopkeeper bookings retrieved successfully',
            'data' => $data
        ], 200);
    }


    /**
     * Booking details for user
     */
    public function bookingDetails($id)
    {
        $booking = ShopBookingRequest::with([
            'shopkeeper:id,name,profile_image',
            'shopkeeper.ratings:id,shopkeeper_id,rating',
            'shopServiceRequest.shop:id,shop_name,shop_image',
            'shopServiceRequest.category:id,name',
            'shopServiceRequest.address:id,name,street,city',
        ])->find($id);

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'No booking found',
                'data' => []
            ], 404);
        }

        $shopkeeper = $booking->shopkeeper;

        $totalCompletedBookings = $shopkeeper
            ? $shopkeeper->shopBookingRequests()
            ->where('status', 'complete_booking')
            ->count()
            : 0;

        $averageRating = $shopkeeper
            ? $shopkeeper->ratings()->avg('rating')
            : 0;

        $data = [
            'request_id'    => $booking->shopServiceRequest->id ?? null,
            'shop_name'     => $booking->shopServiceRequest->shop->shop_name ?? null,
            'shop_image'    => $booking->shopServiceRequest->shop->shop_image
                ? url('shops/' . $booking->shopServiceRequest->shop->shop_image)
                : null,
            'category'      => $booking->shopServiceRequest->category->name ?? null,
            'status'        => $booking->status,
            'file_url'      => $booking->shopServiceRequest->file ?? null,
            'address'       => $booking->shopServiceRequest->address->name ?? null,
            'address_id'    => $booking->shopServiceRequest->address_id ?? null,
            'description'   => $booking->shopServiceRequest->desc ?? null,
            'lang'          => $booking->shopServiceRequest->lang ?? null,
            'lat'           => $booking->shopServiceRequest->lat ?? null,
            'cancelled_by'  => $booking->cancel_by ?? null,
            'cancel_reason' => $booking->cancel_reason ?? null,
            'price'         => $booking->price,
            'starting_date' => $this->formatApiDateTime(optional($booking->shopServiceRequest->created_at)),
            'shopkeeper' => $shopkeeper ? [
                'id' => $shopkeeper->id,
                'name' => $shopkeeper->name,
                'profile_image' => $shopkeeper->profile_image
                    ? url('profiles/' . $shopkeeper->profile_image)
                    : null,
                'total_completed_bookings' => $totalCompletedBookings,
                'average_rating' => round($averageRating, 1),
            ] : null,
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Shop booking details retrieved successfully',
            'data'    => $data
        ], 200);
    }
}
