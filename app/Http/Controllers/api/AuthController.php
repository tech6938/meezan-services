<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\FCMToken;
use App\Models\User;
use App\Services\FcmTokenService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\HttpCache\Store;

class AuthController extends Controller
{

    protected FcmTokenService $fcmService;
    protected ReferralService $referralService;

    public function __construct(FcmTokenService $fcmService, ReferralService $referralService)
    {
        $this->fcmService = $fcmService;
        $this->referralService = $referralService;
    }

    // register
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'      => 'required|string|max:255',
                'phone'     => 'required|string|unique:users,phone',
                'email'     => 'required|string|unique:users,email',
                'password'  => 'required|string|min:6',
                'device_id' => 'nullable|string',
                'fcm_token' => 'required',
                'referral_code' => 'nullable|string|exists:users,referral_code',
            ]);

            $user = DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'      => $request->name,
                    'phone'     => $request->phone,
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password),
                    'device_id' => $request->device_id ?? null,
                    'referral_code' => $this->referralService->generateUniqueReferralCode(),
                ]);

                $this->referralService->assignReferrer($user, $request->input('referral_code'));

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

                return $user;
            });

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

            // Save/update FCM token for this user
            $this->fcmService->saveToken($user->id, 'user', $request->fcm_token);

            // ========== SEND WELCOME NOTIFICATION ==========
            $this->sendWelcomeNotification($user, $request->fcm_token);
            // ==============================================

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

    /**
     * Send welcome notification to user on login
     */
    private function sendWelcomeNotification($user, $fcmToken)
    {
        $title = 'Welcome Back! 👋';
        $body = 'Hello ' . ($user->name ?? 'User') . ', welcome to Mezaan Services!';

        $data = [
            'type' => 'welcome',
            'user_id' => (string)$user->id,
            'receiver_type' => 'user',
            'receiver_id' => (string)$user->id,
            'user_name' => $user->name ?? 'User',
            'login_time' => $this->formatApiDateTime(now()),
            'action' => 'home',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ];

        // Send notification to the logged-in user
        $result = $this->fcmService->sendNotification($fcmToken, $title, $body, $data);

        // if ($result['success']) {
        //     Log::info('Welcome notification sent to user: ' . $user->id);
        // } else {
        //     Log::warning('Failed to send welcome notification: ' . ($result['error'] ?? 'Unknown error'));
        // }
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

            if ($request->hasFile('image')) {

                if (!File::exists(public_path('profiles'))) {
                    File::makeDirectory(public_path('profiles'), 0755, true);
                }

                if ($user->image && File::exists(public_path('profiles/' . $user->image))) {
                    File::delete(public_path('profiles/' . $user->image));
                }

                // Store new image
                $imageName = time() . '_user.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('profiles'), $imageName);

                $user->image = $imageName;
            }

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
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function profile()
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user instanceof User) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $user->loadCount('referrals');
            $user->load('referrer');

            return response()->json([
                'status' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
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
