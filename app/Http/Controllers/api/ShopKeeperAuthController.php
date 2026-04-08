<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\FCMToken;
use App\Models\ShopKeeper;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ShopKeeperAuthController extends Controller
{
    // REGISTER SHOPKEEPER
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'          => 'required|string',
                'phone'         => 'required|unique:shop_keepers,phone',
                'email'         => 'required|email|unique:shop_keepers,email',
                'fcm_token' => 'required',
                'id_front'      => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'id_back'       => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'device_id'     => 'required|string|unique:shop_keepers,device_id',
                'password' => 'required|string|min:6',
            ]);

            DB::beginTransaction();

            $data = [
                'name'       => $request->name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'device_id'  => $request->device_id,
                'status'     => $request->status ?? 'pending',
                'password' => Hash::make($request->password),
            ];

            // Upload files if present
            if ($request->hasFile('id_front')) {
                $data['id_front'] = $request->file('id_front')->store('shopkeeper', 'public');
            }
            if ($request->hasFile('id_back')) {
                $data['id_back'] = $request->file('id_back')->store('shopkeeper', 'public');
            }
            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image')->store('shopkeeper', 'public');
            }

            $shopkeeper = ShopKeeper::create($data);

            // ---- CREATE WALLET WITH 0 BALANCE ----
            Wallet::create([
                'shopkeeper_id' => $shopkeeper->id,
                'amount' => 0,
            ]);

            // fcm token
            FCMToken::updateOrCreate(
                [
                    'entity_type' => 'shopkeeper',
                    'entity_id' => $shopkeeper->id,
                ],
                [
                    'fcm_token' => $request->fcm_token,
                ]
            );

            $token = $shopkeeper->createToken('shopkeeper-token')->plainTextToken;

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Shopkeeper registered successfully',
                'token' => $token,
                'data' => $shopkeeper
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // LOGIN
    public function login(Request $request)
    {
        try {

            $request->validate([
                'phone'     => 'required',
                'device_id'  => 'nullable|string',
                'fcm_token'  => 'required',
                'password'   => 'required',
            ]);

            $shopkeeper = ShopKeeper::where('phone', $request->phone)->first();

            if (!$shopkeeper) {
                return response()->json([
                    'status' => false,
                    'message' => 'Shopkeeper not found'
                ], 404);
            }

            // Status check
            if ($shopkeeper->status == 'blocked' || $shopkeeper->status == 'suspended') {
                return response()->json([
                    'status' => false,
                    'message' => 'Sorry you are blocked or suspended',
                ]);
            }

            if ($shopkeeper->status == 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account verification is pending. Please wait for approval.',
                ]);
            }

            if (!Hash::check($request->password, $shopkeeper->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Update device if provided
            if ($request->filled('device_id')) {
                $shopkeeper->update([
                    'device_id' => $request->device_id
                ]);
            }

            $shopkeeper->tokens()->delete();

            // FCM token update
            FCMToken::updateOrCreate(
                [
                    'entity_type' => 'shopkeeper',
                    'entity_id'   => $shopkeeper->id,
                ],
                [
                    'fcm_token' => $request->fcm_token,
                ]
            );

            // Create new token
            $token = $shopkeeper->createToken('shopkeeper-token')->plainTextToken;

            $shopkeeper['user_type'] = 'shopkeeper';

            return response()->json([
                'status'  => true,
                'message' => 'Login successful',
                'token'   => $token,
                'data'    => $shopkeeper
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // LOGOUT
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
