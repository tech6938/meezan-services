<?php

namespace App\Http\Controllers\api\Provider;

use App\Http\Controllers\Controller;
use App\Models\FCMToken;
use App\Models\Provider;
use App\Models\Wallet;
use App\Services\FcmTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class ProviderRegisterController extends Controller
{

    protected FcmTokenService $fcmService;

    public function __construct(FcmTokenService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string',
            'phone' => 'required|string|unique:providers,phone',
            'email' => 'required|email|unique:providers,email',
            'fcm_token' => 'required',
            'full_name' => 'required|string',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'id_front' => 'required|mimes:jpg,jpeg,png,pdf',
            'id_back' => 'required|mimes:jpg,jpeg,png,pdf',

            'services' => 'required|array',
            'services.*.service_id' => 'required|integer',
            'services.*.sub_services' => 'required|array',
            'services.*.vehicle_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'services.*.vehicle_license' => 'nullable|image|mimes:jpg,jpeg,png',
            'password' => 'required|string|min:6',
        ]);

        if (Provider::where('device_id', $request->device_id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This Device Already Exists',
            ]);
        }
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $baseUrl = url('/'); // Base URL for full paths

        // ---- PROFILE IMAGE (optional) ----
        $profileImageName = null;
        $profileImageUrl = null;

        if ($request->hasFile('profile_image')) {
            if (!File::exists(public_path('profiles'))) {
                File::makeDirectory(public_path('profiles'), 0755, true);
            }

            $profileImageName = time() . '_profile.' . $request->profile_image->getClientOriginalExtension();
            $request->profile_image->move(public_path('profiles'), $profileImageName);

            $profileImageUrl = $baseUrl . '/profiles/' . $profileImageName;
        }

        // ---- ID FRONT (required) ----
        if (!File::exists(public_path('documents'))) {
            File::makeDirectory(public_path('documents'), 0755, true);
        }
        $idFrontName = time() . '_id_front.' . $request->id_front->getClientOriginalExtension();
        $request->id_front->move(public_path('documents'), $idFrontName);
        $idFrontUrl = $baseUrl . '/documents/' . $idFrontName;

        // ---- ID BACK (required) ----
        $idBackName = time() . '_id_back.' . $request->id_back->getClientOriginalExtension();
        $request->id_back->move(public_path('documents'), $idBackName);
        $idBackUrl = $baseUrl . '/documents/' . $idBackName;

        // ---- SERVICES ----
        $servicesData = [];
        foreach ($request->services as $index => $service) {

            // Vehicle Image (optional)
            $vehicleImageUrl = null;
            if (isset($service['vehicle_image']) && $service['vehicle_image']) {
                if (!File::exists(public_path('vehicles'))) {
                    File::makeDirectory(public_path('vehicles'), 0755, true);
                }
                $vehicleImageName = time() . '_vehicle_' . $index . '.' . $service['vehicle_image']->getClientOriginalExtension();
                $service['vehicle_image']->move(public_path('vehicles'), $vehicleImageName);
                $vehicleImageUrl = $baseUrl . '/vehicles/' . $vehicleImageName;
            }

            // Vehicle License (optional)
            $vehicleLicenseUrl = null;
            if (isset($service['vehicle_license']) && $service['vehicle_license']) {
                if (!File::exists(public_path('licenses'))) {
                    File::makeDirectory(public_path('licenses'), 0755, true);
                }
                $vehicleLicenseName = time() . '_license_' . $index . '.' . $service['vehicle_license']->getClientOriginalExtension();
                $service['vehicle_license']->move(public_path('licenses'), $vehicleLicenseName);
                $vehicleLicenseUrl = $baseUrl . '/licenses/' . $vehicleLicenseName;
            }

            $servicesData[] = [
                'service_id' => $service['service_id'],
                'sub_services' => $service['sub_services'],
                'vehicle_image_url' => $vehicleImageUrl,
                'vehicle_license_url' => $vehicleLicenseUrl,
            ];
        }
        DB::beginTransaction();

        try {
            // ---- CREATE PROVIDER ----
            $provider = Provider::create([
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'profile_image' =>  $profileImageName,
                'id_front' => $idFrontName,
                'id_back' => $idBackName,
                'services' => $servicesData,  // store only JSON
                'device_id' => $request->device_id,
                'password' => Hash::make($request->password),
            ]);

            // ----- Fcm Token -----
            FCMToken::updateOrCreate(
                [
                    'entity_type' => 'provider',
                    'entity_id' => $provider->id,
                ],
                [
                    'fcm_token' => $request->fcm_token,
                ]
            );
            // ---- CREATE WALLET WITH 0 BALANCE ----
            Wallet::create([
                'provider_id' => $provider->id,
                'amount' => 0,
            ]);

            $token = $provider->createToken('auth_token')->plainTextToken;

            DB::commit();
            // ---- RESPONSE WITH FULL IMAGE PATHS ----
            return response()->json([
                'status' => true,
                'message' => 'Provider registered successfully',
                'token' => $token,
                'data' => $provider
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required',
                'fcm_token' => 'required',
                'password' => 'required',
            ]);

            $provider = Provider::where('phone', $request->phone)->first();

            if (!$provider) {
                return response()->json([
                    'status' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            if ($provider->status == 'blocked' || $provider->status == 'suspended') {
                return response()->json([
                    'status' => false,
                    'message' => 'Sorry you are blocked or suspended',
                ]);
            }

            if ($provider->status == 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account verification is pending'
                ]);
            }

            // ✅ Password check
            if (!Hash::check($request->password, $provider->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if ($provider) {
                FCMToken::updateOrCreate(
                    [
                        'entity_type' => 'provider',
                        'entity_id' => $provider->id,
                    ],
                    [
                        'fcm_token' => $request->fcm_token,
                    ]
                );
                $this->sendWelcomeNotification($provider, $request->fcm_token);
                // Login existing Provider
                $token = $provider->createToken('auth_token')->plainTextToken;

                $provider['user_type'] = 'provider';

                return response()->json([
                    'status' => true,
                    'message' => 'Provider logged in successfully',
                    'token' => $token,
                    'data' => $provider
                ]);
            } else {
                // Provider not found, do not register
                return response()->json([
                    'status' => false,
                    'message' => 'Provider does not exist. Please register first.'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send welcome notification to user on login
     */
    private function sendWelcomeNotification($user, $fcmToken)
    {
        $title = 'Welcome Back! 👋';
        $body = 'Hello ' . ($user->full_name ?? 'User') . ', welcome to Mezaan Services!';

        $data = [
            'type' => 'welcome',
            'user_id' => (string)$user->id,
            'user_name' => $user->full_name ?? 'User',
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


    public function getProfile()
    {
        $provider = Auth::user();

        if (!$provider) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $provider->id,
                'full_name' => $provider->full_name,
                'phone' => $provider->phone,
                'email' => $provider->email,
                'profile_image_url' => $provider->profile_image_url,
                'id_front_url' => $provider->id_front_url,
                'id_back_url' => $provider->id_back_url,
                'services' => $provider->services,
            ]
        ], 200);
    }



    public function updateProfile(Request $request)
    {
        $provider = Auth::user();

        if (!$provider) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'full_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email|unique:providers,email,' . $provider->id,

            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'id_front' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'id_back' => 'nullable|mimes:jpg,jpeg,png,pdf',

            'services' => 'nullable|array',
            'services.*.service_id' => 'required_with:services|integer',
            'services.*.sub_services' => 'required_with:services|array',
            'services.*.vehicle_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'services.*.vehicle_license' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $baseUrl = url('/');

        // ---- PROFILE IMAGE ----
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($provider->profile_image && File::exists(public_path('profiles/' . basename($provider->profile_image)))) {
                File::delete(public_path('profiles/' . basename($provider->profile_image)));
            }

            if (!File::exists(public_path('profiles'))) {
                File::makeDirectory(public_path('profiles'), 0755, true);
            }

            $profileImageName = time() . '_profile.' . $request->profile_image->getClientOriginalExtension();
            $request->profile_image->move(public_path('profiles'), $profileImageName);
            $provider->profile_image = $profileImageName;
        }

        // ---- ID FRONT ----
        if ($request->hasFile('id_front')) {
            if ($provider->id_front && File::exists(public_path('documents/' . basename($provider->id_front)))) {
                File::delete(public_path('documents/' . basename($provider->id_front)));
            }

            if (!File::exists(public_path('documents'))) {
                File::makeDirectory(public_path('documents'), 0755, true);
            }

            $idFrontName = time() . '_id_front.' . $request->id_front->getClientOriginalExtension();
            $request->id_front->move(public_path('documents'), $idFrontName);
            $provider->id_front = $baseUrl . '/documents/' . $idFrontName;
        }

        // ---- ID BACK ----
        if ($request->hasFile('id_back')) {
            if ($provider->id_back && File::exists(public_path('documents/' . basename($provider->id_back)))) {
                File::delete(public_path('documents/' . basename($provider->id_back)));
            }

            $idBackName = time() . '_id_back.' . $request->id_back->getClientOriginalExtension();
            $request->id_back->move(public_path('documents'), $idBackName);
            $provider->id_back = $baseUrl . '/documents/' . $idBackName;
        }

        // ---- Update basic info ----
        if ($request->filled('full_name')) {
            $provider->full_name = $request->full_name;
        }
        if ($request->filled('phone')) {
            $provider->phone = $request->phone;
        }
        if ($request->filled('email')) {
            $provider->email = $request->email;
        }

        // ---- SERVICES ----
        if ($request->has('services')) {
            $servicesData = [];
            foreach ($request->services as $index => $service) {

                // Vehicle Image
                $vehicleImageUrl = null;
                if (isset($service['vehicle_image']) && $service['vehicle_image']) {
                    if (!File::exists(public_path('vehicles'))) {
                        File::makeDirectory(public_path('vehicles'), 0755, true);
                    }
                    $vehicleImageName = time() . '_vehicle_' . $index . '.' . $service['vehicle_image']->getClientOriginalExtension();
                    $service['vehicle_image']->move(public_path('vehicles'), $vehicleImageName);
                    $vehicleImageUrl = $baseUrl . '/vehicles/' . $vehicleImageName;
                }

                // Vehicle License
                $vehicleLicenseUrl = null;
                if (isset($service['vehicle_license']) && $service['vehicle_license']) {
                    if (!File::exists(public_path('licenses'))) {
                        File::makeDirectory(public_path('licenses'), 0755, true);
                    }
                    $vehicleLicenseName = time() . '_license_' . $index . '.' . $service['vehicle_license']->getClientOriginalExtension();
                    $service['vehicle_license']->move(public_path('licenses'), $vehicleLicenseName);
                    $vehicleLicenseUrl = $baseUrl . '/licenses/' . $vehicleLicenseName;
                }

                $servicesData[] = [
                    'service_id' => $service['service_id'],
                    'sub_services' => $service['sub_services'],
                    'vehicle_image_url' => $vehicleImageUrl,
                    'vehicle_license_url' => $vehicleLicenseUrl,
                ];
            }
            $provider->services = $servicesData;
        }

        $provider->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            // 'data' => $provider
            'data' => [
                'id' => $provider->id,
                'full_name' => $provider->full_name,
                'phone' => $provider->phone,
                'email' => $provider->email,
                'profile_image_url' => $provider->profile_image_url,
                'id_front_url' => $provider->id_front_url,
                'id_back_url' => $provider->id_back_url,
                'services' => $provider->services,
            ]
        ], 200);
    }


    //  providerLogout
    public function providerLogout(Request $request)
    {
        try {
            $provider = Auth::guard('provider-api')->user();

            if (!$provider) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Delete current token only
            $provider->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Provider logged out successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
