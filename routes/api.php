<?php

use App\Http\Controllers\api\{AddressController, AuthController, ChatController, NotificationController, ServiceRequestController, CategoryController, ProviderDashController, SMSController, UploadController};
use App\Http\Controllers\api\{ShopKeeperAuthController, NearbyShopController, WalletController};
use App\Http\Controllers\api\Provider\BookingRequestController;
use App\Http\Controllers\api\Provider\ProviderRegisterController;
use App\Http\Controllers\api\RatingController;
use App\Http\Controllers\api\ResetPassController;
use App\Http\Controllers\api\SettingController;
use App\Http\Controllers\api\ShopController;
use App\Http\Controllers\ReferralController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OTP Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['cors'])->group(function () {
    Route::controller(SMSController::class)->group(function () {
        Route::post('/send-otp', 'sendOtp')->name('send-otp');
        Route::post('/verify-otp', 'verifyOtp')->name('verify-otp');
    });

    /*
    |--------------------------------------------------------------------------
    | RESET PASSWORD FOR (USER, PROVIDER, SHOPKEEPER)
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:3,1')->group(function () {
        Route::post('send-reset-otp', [ResetPassController::class, 'sendResetOtp']);
    });

    Route::post('verify-reset-otp', [ResetPassController::class, 'verifyResetOtp']);
    Route::post('reset-password', [ResetPassController::class, 'resetPassword']);

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::controller(SettingController::class)->group(function () {
        Route::get('app_url', 'appUrl');
        Route::get('appSetting', 'getApiSettings');
    });

    /*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
    // User Authentication
    Route::controller(AuthController::class)->group(function () {
        Route::post('/auth', 'auth');
        Route::post('/register', 'register');
        Route::get('/user/status', 'userStatus');
        Route::post('/fcm_token', 'fcm_token');
        Route::delete('/customer/delete-account', 'deleteAccount');
    });

    // Provider Authentication
    Route::controller(ProviderRegisterController::class)->group(function () {
        Route::post('providerRegister', 'register');
        Route::post('providerLogin', 'login');
        Route::get('provider/status', 'providerStatus');
        Route::delete('/provider/delete-account', 'deleteAccount');
    });

    // ShopKeeper Authentication
    Route::controller(ShopKeeperAuthController::class)->group(function () {
        Route::post('/shopkeeper/register', 'register');
        Route::post('/shopkeeper/login', 'login');
    });
    Route::controller(ProviderDashController::class)->group(function () {
        Route::get('previous-work/{type}/{id}', 'previousWorkById');
    });
    /*
|--------------------------------------------------------------------------
| Category Routes (Public)
|--------------------------------------------------------------------------
*/
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/MainCategories', 'MainCategories');
        Route::get('/SubCategories/{id}', 'SubCategoriesByMain');
        Route::get('/allCategories', 'allCategories');
    });

    /*
|--------------------------------------------------------------------------
| Booking Request Routes (Public)
|--------------------------------------------------------------------------
*/
    Route::controller(BookingRequestController::class)->group(function () {
        Route::get('/all-requests', 'allRequests');
        Route::get('/request-details/{id}', 'providerRequestDetails');
        Route::post('/mark-as-seen', 'markBookingAsSeen');
        Route::post('request/mark-as-seen', 'markRequestAsSeen');
    });

    /*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
    Route::middleware('auth:sanctum')->group(function () {

        // Nearby Shop
        Route::get('/shop/nearby', [NearbyShopController::class, 'nearbyShop']);

        // User & Provider Logout
        Route::group([], function () {
            Route::post('/user/logout', [AuthController::class, 'userLogout']);
            Route::post('/provider/logout', [ProviderRegisterController::class, 'providerLogout']);
        });

        // Profile Management
        Route::controller(AuthController::class)->group(function () {
            Route::get('/profile', 'profile');
            Route::post('/profileUpdate/{id}', 'ProfileUpdate');
            Route::post('/updateAuth/{id}', 'updateAuth');
        });

        // Service Request Routes
        Route::controller(ServiceRequestController::class)->group(function () {
            Route::get('/service-request/{id}', 'serviceRequestDetails');
            Route::get('/ServiceRequest', 'ServiceRequest');
            Route::post('/ServiceRequest/store', 'ServiceRequestStore');
            Route::post('/ServiceRequest/updateStatus/{id}', 'updateStatus');
        });


        // Address Routes
        Route::controller(AddressController::class)->group(function () {
            Route::get('/address', 'address');
            Route::post('/address/store', 'storeAddress');
            Route::post('/address/update/{id}', 'updateAddress');
            Route::delete('/address/delete/{id}', 'addressDelete');
        });

        // Chat Routes
        Route::controller(ChatController::class)->group(function () {
            Route::get('chatList', 'chatList');
            Route::get('chat/{receiverType}/{receiverId}', 'chatWithUser');
            Route::post('/chat/send', 'sendMessage');
            Route::post('/mark-as-read', 'markAsSeen');
        });

        // Route::controller(ReferralController::class)->group(function () {
        //     Route::get('/my-referral-code', 'myReferralCode');
        //     Route::get('/my-referral-tree', 'myReferralTree');
        //     Route::get('/my-referral-earnings', 'myReferralEarnings');
        //     Route::get('/my-referral-history', 'myReferralHistory');
        // });
        Route::prefix('referral')->group(function () {
            // Validate referral code
            Route::post('validate', [ReferralController::class, 'validateCode']);

            // Apply referral code
            Route::post('apply', [ReferralController::class, 'apply']);

            // Get user's referral info
            Route::get('info', [ReferralController::class, 'getInfo'])->middleware('auth:sanctum');

            // Separate endpoints for UI tiles
            Route::get('lifetime', [ReferralController::class, 'lifetimeEarning'])->middleware('auth:sanctum');
            Route::get('available', [ReferralController::class, 'availableEarning'])->middleware('auth:sanctum');
            Route::get('withdrawn', [ReferralController::class, 'amountWithdrawn'])->middleware('auth:sanctum');
            Route::get('shared', [ReferralController::class, 'appSharedWith'])->middleware('auth:sanctum');
            Route::get('completed-bookings', [ReferralController::class, 'completedBookings'])->middleware('auth:sanctum');
            // Combined summary API returning all keys
            Route::get('transactions', [ReferralController::class, 'refferalTrans'])->middleware('auth:sanctum');
            Route::get('summary', [ReferralController::class, 'summary'])->middleware('auth:sanctum');
            // Overview: dashboard metrics + transactions
            Route::get('overview', [ReferralController::class, 'overview']);
            // Referral transaction history (from referral_logs)
        });

        // Upload Routes
        Route::controller(UploadController::class)->group(function () {
            Route::post('/upload', 'upload');
            Route::get('/upload/list', 'list');
        });

        // Notification Routes
        Route::controller(NotificationController::class)->group(function () {
            Route::get('/notifications', 'index');
            Route::post('/notifications/mark-as-read', 'markAsRead');
        });

        // Provider Profile Routes
        Route::controller(ProviderRegisterController::class)->group(function () {
            Route::get('providerProfile', 'getProfile');
            Route::post('profileUpdate', 'updateProfile');
        });

        // Booking Request Routes
        Route::controller(BookingRequestController::class)->group(function () {
            Route::post('/booking-complete', 'completeBookingStatus');
            Route::post('accept-request', 'providerAcceptRequest');
            Route::post('cancel-request', 'userCancelRequest');
            Route::post('shop-booking/accept-request', 'shopkeeperAcceptRequest');
            Route::get('my-bookings', 'myBookings');
            Route::get('user-bookings', 'userBookings');
            Route::get('provider/booking-details/{id}', 'providerBookingDetails');
            Route::get('booking-details/{id}', 'bookingDetails');
            Route::post('updateBookingStatus', 'startBooking');
            Route::post('come', 'comming');
            Route::post('goto', 'goto');
        });

        // Provider Dashboard Routes
        Route::controller(ProviderDashController::class)->group(function () {
            Route::get('providerDashboard', 'providerDashboard');
            Route::post('previousWork', 'previousWork');
            Route::get('previousWorkDetail', 'previousWorkDetail');
        });

        // ShopKeeper Logout
        Route::post('/shopkeeper/logout', [ShopKeeperAuthController::class, 'logout']);

        // Shop Routes
        Route::controller(ShopController::class)->group(function () {
            Route::post('/shop/add', 'shop');
            Route::get('/user/shops', 'userShops');
            Route::delete('/shop/delete/{id}', 'shopDelete');
        });

        /*
    |--------------------------------------------------------------------------
    | Shop Service Request Routes (User Side) - NEW SHOPKEEPER SYSTEM
    |--------------------------------------------------------------------------
    */
        // Route::prefix('shop-service-request')->controller(ShopRequestController::class)->group(function () {
        //     Route::get('/', 'index');
        //     Route::get('/{id}', 'show');
        //     Route::post('/store', 'store');
        //     Route::post('/update-status/{id}', 'updateStatus');
        // });

        // /*
        // |--------------------------------------------------------------------------
        // | Shop Booking Routes (Shopkeeper & User Side) - NEW SHOPKEEPER SYSTEM
        // |--------------------------------------------------------------------------
        // */
        // Route::prefix('shop-booking')->controller(ShopBookingRequestController::class)->group(function () {
        //     // Shopkeeper actions
        //     Route::post('/accept-request', 'acceptRequest');
        //     Route::get('/my-bookings', 'myBookings');
        //     Route::get('/shopkeeper/details/{id}', 'shopkeeperBookingDetails');
        //     Route::post('/update-status', 'startBooking');
        //     Route::post('/complete', 'completeBookingStatus');
        // });

        // Rating Route
        Route::post('/rating', [RatingController::class, 'rating']);

        /*
    |-----------------------------------b---------------------------------------
    | WALLET SYSTEM
    |--------------------------------------------------------------------------
    */
        Route::controller(WalletController::class)->group(function () {
            Route::post('/deposit', 'deposit');
            Route::get('/myWallet', 'myWallet');
            Route::get('/transactionHistory', 'transactionHistory');
        });
    });
});
