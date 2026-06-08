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

class ServiceRequestController extends Controller
{
    // get user all requests
    // public function ServiceRequest()
    // {
    //     try {
    //         $user_id = Auth::user()->id;

    //         $serviceRequests = ServiceRequest::with([
    //             'category:id,name',
    //             'subCategory:id,name',
    //             'bookingRequests.provider:id,full_name',
    //             'bookingRequests.shopkeeper:id,name',
    //             'shop:id,shop_name,category',
    //         ])
    //             ->where('user_id', $user_id)
    //             ->orderBy('id', 'desc')
    //             ->get()
    //             ->map(function ($request) {

    //                 // 👉 Get latest booking OR you can customize logic
    //                 $booking = $request->bookingRequests->first();

    //                 return [
    //                     'id' => $request->id,
    //                     'cat_name' => optional($request->category)->name,
    //                     'subcat_name' => optional($request->subCategory)->name,
    //                     'shop_name' => optional($request->shop)->shop_name,
    //                     'shop_cat' => optional($request->shop)->category,
    //                     'desc' => $request->desc,

    //                     // STATUS from booking_requests table
    //                     'status' => optional($booking)->req_status ?? 'pending',
    //                     // 'status' => optional($request)->status, //according to old system

    //                     'created_at' => $request->created_at,

    //                     // Provider Name
    //                     'provider_name' => optional(optional($booking)->provider)->full_name,

    //                     // Shopkeeper Name
    //                     'shopkeeper_name' => optional(optional($booking)->shopkeeper)->name,
    //                 ];
    //             });

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Service requests fetched successfully',
    //             'data' => $serviceRequests
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

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

                    // 👉 Get latest booking OR you can customize logic
                    $booking = $request->bookingRequests->first();

                    return [
                        'id' => $request->id,
                        'cat_name' => optional($request->category)->name,
                        'subcat_name' => optional($request->subCategory)->name,
                        'shop_name' => optional($request->shop)->shop_name,
                        'shop_cat' => optional($request->shop)->category,
                        'desc' => $request->desc,

                        // STATUS: if booking exists -> use booking status, else use service request status
                        'status' => $booking ? $booking->req_status : $request->status,

                        'created_at' => $request->created_at,

                        // Provider Name
                        'provider_name' => optional(optional($booking)->provider)->full_name,

                        // Shopkeeper Name
                        'shopkeeper_name' => optional(optional($booking)->shopkeeper)->name,
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
    // public function ServiceRequestStore(Request $request)
    // {
    //     try {
    //         // Validate request with 'file' as array
    //         $validatedData = $request->validate([
    //             'cat_id' => 'nullable|integer',
    //             'subcat_id' => 'nullable|integer',
    //             'address_id' => 'nullable|integer',
    //             'shop_id' => 'nullable|integer',
    //             'lang' => 'required|string',
    //             'lat' => 'required|string',
    //             'desc' => 'nullable|string',
    //             'file' => 'nullable|array',
    //             'file.*' => 'file|max:102400',
    //             'status' => 'nullable|string',
    //         ]);

    //         $filePaths = [];
    //         if ($request->hasFile('file')) {   // use 'file' here (same as validation)
    //             foreach ($request->file('file') as $file) {
    //                 $fileName = time() . '_' . $file->getClientOriginalName();
    //                 $uploadDir = public_path('uploads');

    //                 if (!file_exists($uploadDir)) {
    //                     mkdir($uploadDir, 0777, true);
    //                 }

    //                 $file->move($uploadDir, $fileName);
    //                 $filePaths[] = 'uploads/' . $fileName;
    //             }
    //         }

    //         // Replace 'file' array in validated data with JSON string of file paths
    //         $validatedData['file'] = json_encode($filePaths);

    //         // Add logged-in user's ID
    //         $validatedData['user_id'] = $request->user()->id;

    //         // Create the service request record
    //         $serviceRequest = ServiceRequest::create($validatedData);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Service request created successfully',
    //             'data' => $serviceRequest,
    //         ], 201);
    //     } catch (\Illuminate\Validation\ValidationException $ve) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Validation error',
    //             'errors' => $ve->errors()
    //         ], 422);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

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
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {
                    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                    $uploadDir = public_path('uploads');

                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $file->move($uploadDir, $fileName);
                    $filePaths[] = 'uploads/' . $fileName;
                }
            }

            // Convert file paths array to JSON string for database storage
            $validatedData['file'] = !empty($filePaths) ? json_encode($filePaths) : null;

            // Add logged-in user's ID
            $validatedData['user_id'] = $request->user()->id;

            // Create the service request record
            $serviceRequest = ServiceRequest::create($validatedData);

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
    // public function updateStatus(Request $request, $id)
    // {
    //     try {
    //         // Validate the request
    //         $validatedData = $request->validate([
    //             'status' => 'required|string', // adjust allowed statuses as needed
    //         ]);

    //         // Find the service request
    //         $serviceRequest = ServiceRequest::find($id);

    //         if (!$serviceRequest) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Service request not found',
    //             ], 404);
    //         }

    //         // Update the status
    //         $serviceRequest->status = $validatedData['status'];
    //         $serviceRequest->save();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Status updated successfully',
    //             'data' => [
    //                 'status' => $serviceRequest->status,
    //             ],
    //         ], 200);
    //     } catch (\Illuminate\Validation\ValidationException $ve) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Validation error',
    //             'errors' => $ve->errors()
    //         ], 422);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function updateStatus(Request $request, $id)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'status' => 'required|string|in:pending,accept,in_progress,complete_booking,cancel',
            ]);

            // Find the service request
            $serviceRequest = ServiceRequest::find($id);

            if (!$serviceRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service request not found',
                ], 404);
            }

            // Check if any booking requests exist for this request_id
            $bookingExists = BookingRequest::where('request_id', $id)->exists();

            // Check if trying to cancel
            if ($validatedData['status'] == 'cancel') {
                if ($bookingExists) {
                    // Booking already exists, cannot cancel
                    return response()->json([
                        'status' => false,
                        'message' => 'Booking is created for this order',
                    ], 400);
                } else {
                    // No booking exists, can cancel the service request
                    $serviceRequest->status = 'cancel';
                    $serviceRequest->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Service request cancelled successfully',
                        'data' => [
                            'status' => $serviceRequest->status,
                        ],
                    ], 200);
                }
            }

            // For non-cancel status updates
            $updatedCount = 0;
            $updatedIn = '';

            if ($bookingExists) {
                // Update all booking requests with this request_id
                $updatedCount = BookingRequest::where('request_id', $id)
                    ->update(['req_status' => $validatedData['status']]);
                $updatedIn = 'booking_requests';

                // Get the updated status from booking request for response
                $updatedStatus = $validatedData['status'];
            } else {
                // No booking records found, update service request table
                $serviceRequest->status = $validatedData['status'];
                $serviceRequest->save();
                $updatedCount = 1;
                $updatedIn = 'service_requests';
                $updatedStatus = $serviceRequest->status;
            }

            return response()->json([
                'status' => true,
                'message' => "Status updated successfully",
                'data' => [
                    'status' => $updatedStatus,
                ],
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
    // public function serviceRequestDetails($id)
    // {
    //     try {
    //         $user_id = Auth::id();

    //         $serviceRequest = ServiceRequest::with([
    //             'category:id,name',
    //             'subCategory:id,name',
    //             'shop:id,shop_name,category',
    //             'address:id,name,street,city,PostalCode',
    //             'bookingRequest.provider.ratings',
    //             'bookingRequest.shopkeeper.ratings',
    //         ])
    //             ->where('user_id', $user_id)
    //             ->find($id);

    //         if (!$serviceRequest) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Service request not found'
    //             ], 404);
    //         }

    //         /* Address */
    //         $address = null;
    //         if ($serviceRequest->address) {
    //             $address = collect([
    //                 $serviceRequest->address->name,
    //                 $serviceRequest->address->street,
    //                 $serviceRequest->address->city,
    //                 $serviceRequest->address->PostalCode,
    //             ])->filter()->implode(', ');
    //         }

    //         /* ---------------- Provider Data ---------------- */
    //         $providerData = null;

    //         if (
    //             $serviceRequest->status === 'accept' &&
    //             $serviceRequest->bookingRequest &&
    //             $serviceRequest->bookingRequest->provider
    //         ) {
    //             $provider = $serviceRequest->bookingRequest->provider;

    //             $totalCompletedBookings = $provider->bookingRequests()
    //                 ->where('status', 'complete_booking')
    //                 ->count();

    //             $averageRating = $provider->ratings()->avg('rating');

    //             $providerData = [
    //                 'id' => $provider->id,
    //                 'name' => $provider->full_name,
    //                 'profile_image' => $provider->profile_image
    //                     ? url('profiles/' . $provider->profile_image)
    //                     : null,
    //                 'total_completed_bookings' => $totalCompletedBookings,
    //                 'average_rating' => $averageRating
    //                     ? round($averageRating, 1)
    //                     : 0,
    //             ];
    //         }

    //         /* ---------------- Shopkeeper Data ---------------- */
    //         $shopkeeperData = null;

    //         if (
    //             $serviceRequest->status === 'accept' &&
    //             $serviceRequest->bookingRequest &&
    //             $serviceRequest->bookingRequest->shopkeeper
    //         ) {
    //             $shopkeeper = $serviceRequest->bookingRequest->shopkeeper;

    //             $totalCompletedBookings = $shopkeeper->bookingRequests()
    //                 ->where('status', 'complete_booking')
    //                 ->count();

    //             $averageRating = $shopkeeper->ratings()->avg('rating');

    //             $shopkeeperData = [
    //                 'id' => $shopkeeper->id,
    //                 'name' => $shopkeeper->name,
    //                 'profile_image' => $shopkeeper->profile_image
    //                     ?? null,
    //                 'total_completed_bookings' => $totalCompletedBookings,
    //                 'average_rating' => $averageRating
    //                     ? round($averageRating, 1)
    //                     : 0,
    //             ];
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Service request details retrieved successfully',
    //             'data' => [
    //                 'id' => $serviceRequest->id,
    //                 'user_id' => $serviceRequest->user_id,
    //                 'cat_name' => optional($serviceRequest->category)->name,
    //                 'subcat_name' => optional($serviceRequest->subCategory)->name,
    //                 'shop_name' => optional($serviceRequest->shop)->shop_name,
    //                 'shop_cat' => optional($serviceRequest->shop)->category,
    //                 'address' => $address,
    //                 'address_id' => $serviceRequest->address_id,
    //                 'lang' => $serviceRequest->lang,
    //                 'lat' => $serviceRequest->lat,
    //                 'desc' => $serviceRequest->desc,
    //                 'file' => $serviceRequest->file,
    //                 'file_type' => $serviceRequest->file_type,
    //                 'status' => $serviceRequest->status,
    //                 'created_at' => $serviceRequest->created_at,
    //                 'provider' => $providerData,
    //                 'shopkeeper' => $shopkeeperData,
    //             ]
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


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

            $allowedStatuses = ['accept', 'in_progress', 'complete_booking'];
            $filteredBookingRequests = $serviceRequest->bookingRequests
                ->whereIn('req_status', $allowedStatuses);

            foreach ($filteredBookingRequests as $bookingRequest) {
                // Provider data with unread count
                if ($bookingRequest->provider) {
                    $provider = $bookingRequest->provider;

                    // Get unread count for this provider in this booking
                    $providerUnreadCount = Chat::where('booking_id', $bookingRequest->id)
                        ->where('receiver_id', $provider->id)
                        ->where('receiver_type', 'App\Models\Provider')
                        ->where('is_seen', false)
                        ->whereNull('deleted_at')
                        ->count();

                    $totalCompletedBookings = $provider->bookingRequests()
                        ->where('req_status', 'complete_booking')
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
                        'status' => $bookingRequest->req_status,
                        'unread_count' => $providerUnreadCount, // Add unread count
                        'booking_id' => $bookingRequest->id,
                    ];
                }

                // Shopkeeper data with unread count
                if ($bookingRequest->shopkeeper) {
                    $shopkeeper = $bookingRequest->shopkeeper;

                    // Get unread count for this shopkeeper in this booking
                    $shopkeeperUnreadCount = Chat::where('booking_id', $bookingRequest->id)
                        ->where('receiver_id', $shopkeeper->id)
                        ->where('receiver_type', 'App\Models\ShopKeeper')
                        ->where('is_seen', false)
                        ->whereNull('deleted_at')
                        ->count();

                    $totalCompletedBookings = $shopkeeper->bookingRequests()
                        ->where('req_status', 'complete_booking')
                        ->count();

                    $averageRating = $shopkeeper->ratings()->avg('rating');

                    $shopkeepersList[] = [
                        'id' => $shopkeeper->id,
                        'name' => $shopkeeper->name,
                        'profile_image' => $shopkeeper->profile_image ?? null,
                        'total_completed_bookings' => $totalCompletedBookings,
                        'average_rating' => $averageRating ? round($averageRating, 1) : 0,
                        'status' => $bookingRequest->req_status,
                        'unread_count' => $shopkeeperUnreadCount, // Add unread count
                        'booking_id' => $bookingRequest->id,
                    ];
                }
            }

            $currentBooking = $serviceRequest->bookingRequests
                ->whereIn('req_status', ['in_progress', 'accept'])
                ->first();

            $status = $currentBooking ? $currentBooking->req_status : $serviceRequest->status;

            return response()->json([
                'status' => true,
                'message' => 'Service request details retrieved successfully',
                'data' => [
                    'id' => $serviceRequest->id,
                    'booking_id' => $currentBooking?->id ?? 'null',
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
                    'status' => $status,
                    'created_at' => $serviceRequest->created_at,
                    'providers' => $providersList,
                    'shopkeepers' => $shopkeepersList,
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
