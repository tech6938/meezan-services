<?php

namespace App\Http\Controllers\api;

use App\Models\Rating;
use App\Models\Previous;
use App\Models\Provider;
use App\Models\ShopKeeper;
use Illuminate\Http\Request;
use App\Models\BookingRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProviderDashController extends Controller
{
    // providerDashboard
    public function providerDashboard()
    {
        try {
            $provider_id = auth('provider-api')->id();
            $totalbookings = BookingRequest::where('provider_id', $provider_id)->get();
            $pending = BookingRequest::where('provider_id', $provider_id)->where('status', 'pending')
            ->where('assigned', 1)
            ->where('goto', 2)
            ->get();
            $cancel = BookingRequest::where('provider_id', $provider_id)->where('status', 'cancel')->get();
            $completed = BookingRequest::where('provider_id', $provider_id)->where('status', 'complete_booking')->get();

            // Get completed booking IDs
            $completedIds = $completed->pluck('id');

            // Calculate total booking price for completed bookings
            $totalBookingPrice = $completed->sum('price');

            // Calculate total commission deducted from commission_logs
            $totalCommissionDeducted = DB::table('commission_logs')
                ->whereIn('booking_id', $completedIds)
                ->where('provider_id', $provider_id)
                ->sum('commission_deducted');

            // Total earnings = total booking price - total commission deducted
            $totalEarning = $totalBookingPrice - $totalCommissionDeducted;

            $data = [
                'total_bookings' => count($totalbookings),
                'pending' => count($pending),
                'cancel' => count($cancel),
                'compeleted' => count($completed),
                'total_earning' => number_format($totalEarning, 2),
                'app_commission' => number_format($totalCommissionDeducted, 2),
            ];
            return response()->json([
                'status' => true,
                'message' => 'All Booking Details Are Retrieved Successfuly',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // previousWork
    public function previousWork(Request $request)
    {
        try {
            $provider = Auth::guard('provider-api')->user();
            $shopkeeper = Auth::guard('shopkeeper-api')->user();

            if (!$provider && !$shopkeeper) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->validate([
                'images' => 'required|array',
                'images.*' => 'image|mimes:jpg,jpeg,png,gif,svg,jfif|max:2048',
            ]);

            $imagePaths = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('previousWork', 'public');
                    $imagePaths[] = $path;
                }
            }

            $previous = new Previous();

            if ($provider) {
                $previous->provider_id = $provider->id;
            }

            if ($shopkeeper) {
                $previous->shopkeeper_id = $shopkeeper->id;
            }

            $previous->images = $imagePaths;
            $previous->save();

            return response()->json([
                'status' => true,
                'message' => 'Previous work uploaded successfully',
                'data' => $previous
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // previousWorkDetail
    // public function previousWorkDetail()
    // {
    //     try {
    //         $auth_id = Auth::id();

    //         // Get provider
    //         $provider = Provider::select('id', 'full_name', 'profile_image')->find($auth_id);

    //         if (!$provider) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Provider not found'
    //             ], 404);
    //         }

    //         // Get average rating
    //         $avg_rating = Rating::where('provider_id', $auth_id)->avg('rating');
    //         // Get all previous work images
    //         $previousImages = Previous::where('provider_id', $auth_id)
    //             ->pluck('images') // gets array of arrays
    //             ->flatten()       // flatten into a single array
    //             ->values();       // reindex the array

    //         // Prepare response data
    //         $data = [
    //             'full_name' => $provider->full_name,
    //             'profile_image' => $provider->profile_image,
    //             'avg_rating' => $avg_rating ? round($avg_rating, 2) : null,
    //             'previous_work_images' => $previousImages
    //         ];

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Provider details retrieved successfully',
    //             'data' => $data
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function previousWorkDetail()
    {
        try {
            $provider   = Auth::guard('provider-api')->user();
            $shopkeeper = Auth::guard('shopkeeper-api')->user();

            if (!$provider && !$shopkeeper) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            /* ---------------- Provider Case ---------------- */
            if ($provider) {

                $providerData = Provider::select('id', 'full_name', 'profile_image')
                    ->find($provider->id);

                if (!$providerData) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Provider not found'
                    ], 404);
                }

                $avg_rating = Rating::where('provider_id', $provider->id)
                    ->avg('rating');

                $previousImages = Previous::where('provider_id', $provider->id)
                    ->pluck('images')
                    ->flatten()
                    ->values();

                $data = [
                    'user_type' => 'provider',
                    'full_name' => $providerData->full_name,
                    'profile_image' => asset('profiles/'.$providerData->profile_image) ?? null,
                    'avg_rating' => $avg_rating ? round($avg_rating, 2) : null,
                    'previous_work_images' => $previousImages
                ];
            }

            /* ---------------- Shopkeeper Case ---------------- */
            if ($shopkeeper) {

                $avg_rating = Rating::where('shopkeeper_id', $shopkeeper->id)
                    ->avg('rating');

                $previousImages = Previous::where('shopkeeper_id', $shopkeeper->id)
                    ->pluck('images')
                    ->flatten()
                    ->values();

                $data = [
                    // 'user_type' => 'shopkeeper',
                    'full_name' => $shopkeeper->name,
                    'profile_image' => $shopkeeper->profile_image ?? null,
                    'avg_rating' => $avg_rating ? round($avg_rating, 2) : null,
                    'previous_work_images' => $previousImages
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Details retrieved successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // previousWorkDetail by id
    public function previousWorkById($type, $id)
    {
        try {

            /* ---------- Provider ---------- */
            if ($type === 'provider') {

                $previousImages = Previous::where('provider_id', $id)->first();
                if (!$previousImages) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No previous work found for this provider'
                    ], 404);
                }
                $provider = Provider::select('id', 'full_name', 'profile_image')
                    ->find($id);

                if (!$provider) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Provider not found'
                    ], 404);
                }

                $avg_rating = Rating::where('provider_id', $id)
                    ->avg('rating');

                $previousImages = Previous::where('provider_id', $id)
                    ->pluck('images')
                    ->flatten()
                    ->values();

                $data = [
                    'user_type' => 'provider',
                    'full_name' => $provider->full_name,
                    'profile_image' => $provider->profile_image,
                    'avg_rating' => $avg_rating ? round($avg_rating, 2) : null,
                    'previous_work_images' => $previousImages
                ];
            }

            /* ---------- Shopkeeper ---------- */ elseif ($type === 'shopkeeper') {
                $previousImages = Previous::where('shopkeeper_id', $id)->first();
                if (!$previousImages) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No previous work found for this shopkeeper'
                    ], 404);
                }

                $shopkeeper = ShopKeeper::select('id', 'name', 'profile_image')
                    ->find($id);

                if (!$shopkeeper) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Shopkeeper not found'
                    ], 404);
                }

                $avg_rating = Rating::where('shopkeeper_id', $id)
                    ->avg('rating');

                $previousImages = Previous::where('shopkeeper_id', $id)
                    ->pluck('images')
                    ->flatten()
                    ->values();

                $data = [
                    'user_type' => 'shopkeeper',
                    'full_name' => $shopkeeper->name,
                    'profile_image' => $shopkeeper->profile_image,
                    'avg_rating' => $avg_rating ? round($avg_rating, 2) : null,
                    'previous_work_images' => $previousImages
                ];
            }

            /* ---------- Invalid Type ---------- */ else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid type. Use provider or shopkeeper.'
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'Details retrieved successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
