<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\FCMToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\HttpCache\Store;

class AuthController extends Controller
{

    // register
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'      => 'required|string|max:255',
                'phone'     => 'required|string|unique:users,phone',
                'password'  => 'required|string|min:6',
                'device_id' => 'required|string|unique:users,device_id',
                'fcm_token' => 'required',
            ]);

            $user = User::create([
                'name'      => $request->name,
                'phone'     => $request->phone,
                'password'  => Hash::make($request->password), // 🔐 Hashing password
                'device_id' => $request->device_id,
            ]);

            // Save FCM token
            FCMToken::updateOrCreate(
                [
                    'entity_type' => 'user',
                    'entity_id'   => $user->id,
                ],
                [
                    'fcm_token' => $request->fcm_token,
                ]
            );

            // Create Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status'  => true,
                'message' => 'User Signup Successfuly',
                'token'   => $token,
                'data'    => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }


    // login
    public function auth(Request $request)
    {
        try {
            $request->validate([
                'phone'      => 'required',
                'password'   => 'required',
                'fcm_token'  => 'required',
            ]);

            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User Not Found',
                ]);
            }

            // Check blocked status
            if ($user->status === 'blocked') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Sorry You Are Blocked',
                ]);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid Credentials',
                ]);
            }

            // Update FCM token
            FCMToken::updateOrCreate(
                [
                    'entity_type' => 'user',
                    'entity_id'   => $user->id,
                ],
                [
                    'fcm_token' => $request->fcm_token,
                ]
            );

            // Create Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status'  => true,
                'message' => 'User logged in',
                'token'   => $token,
                'data'    => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage(),
            ]);
        }
    }


    // update auth
    public function ProfileUpdate(Request $request, $id)
    {
        try {
            // Find the user
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Validate request (optional but recommended)
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
            ]);

            // Handle file upload if provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }

                // Store new image and save path
                $path = $request->file('image')->store('new', 'public');
                $user->image = $path;
            }

            // Update other fields if provided
            if ($request->filled('name')) {
                $user->name = $request->name;
            }
            if ($request->filled('phone')) {
                $user->phone = $request->phone;
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => $user, // accessor will automatically give full image URL
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // fcm token
    public function fcm_token(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'lang' => 'nullable',
            'lat' => 'nullable',
        ]);

        $admin = Auth::user();

        $admin->update([
            'fcm_token' => $request->fcm_token,
            'lang' => $request->lang ?? null,
            'lat' => $request->lat ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'FCM token saved successfully',
        ]);
    }
    // userLogout
    public function userLogout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
