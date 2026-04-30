<?php

use App\Http\Controllers\{AuthController, BookingController, MainCategoryController, SettingController, SubCategoryController, TaxController, VolunteerController};
use App\Http\Controllers\api\RatingController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserProviderController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;
// dash
Route::middleware([AuthMiddleware::class])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    // volunteers routes
    // Route::resource('volunteer', VolunteerController::class);
});

Route::controller(AuthController::class)->group(function () {
    // Login
    Route::get('/', 'login')->name('login');
    Route::post('/match-login', 'match_login')->name('match-login');
    // Signup
    Route::get('/signup', 'signup')->name('signup');
    Route::post('/insert-signup', 'insert_signup')->name('insert-signup');
    // Logout
    Route::post('/logout', 'logout')->name('logout');
    // Forgot password
    Route::get('/forget', 'forget')->name('forget');
    Route::post('/forget-message', 'forget_message')->name('forget_message');
    // OTP
    Route::get('/otp', 'otp')->name('otp');
    Route::post('/matching-route', 'matching_route')->name('matching_route');
    // Reset password
    Route::get('/reset', 'reset')->name('reset');
    Route::post('/reset-password', 'update_password')->name('reset_password');
    // for main categories and mainCategoriesDetails
    Route::resource('/main-categories', MainCategoryController::class);
    // for sub category
    Route::resource('/sub-categories',  SubCategoryController::class);
    // for chat list
    Route::get('/chat-list',  [ChatsController::class, 'chatsList'])->name('chatsList');
    Route::get('/chats/{sender_type}/{sender_id}/{receiver_type}/{receiver_id}', [ChatsController::class, 'chatBetween'])->name('chats.between');
    Route::post('/admin/chats/export', [ChatsController::class, 'exportSelectedChats'])->name('chats.export');

    /*
|--------------------------------------------------------------------------
| Users & Providers Routes
|--------------------------------------------------------------------------
*/
    Route::controller(UserProviderController::class)->group(function () {
        // for users list and viewUserDetail and update status
        Route::get('/users-list', 'userList')->name('userList');
        Route::get('/view-user-detail/{id}', 'viewUserDetail')->name('viewUserDetail');
        Route::post('/updateUserStatus', 'updateUserStatus')->name('updateUserStatus');
        Route::delete('/user/{id}', 'userDestroy')->name('user.destroy');

        // for providers list
        Route::get('/approved-providers', 'approvedProviders')->name('approvedProviders');
        Route::get('/blocked-providers', 'blockedProviders')->name('blockedProviders');
        Route::get('/suspended-providers', 'suspendedProviders')->name('suspendedProviders');
        Route::get('/pending-providers', 'pendingProviders')->name('pendingProviders');
        Route::get('/all-providers', 'allProviders')->name('allProviders');
        Route::get('/provider/details/{id}', 'viewProviderDetail')->name('provider.details');
        Route::post('/statusUpdate', 'statusUpdate')->name('statusUpdate');
        Route::delete('/provider/{id}', 'destroy')->name('provider.destroy');
    });

    /*
|--------------------------------------------------------------------------
| Service Requests Routes
|--------------------------------------------------------------------------
*/
    Route::controller(ServiceRequestController::class)->group(function () {

        // all service request service requests
        Route::get('/service-requests', 'allRequest')->name('allRequest');
        Route::get('/pending-request', 'pendingRequest')->name('pendingRequest');
        Route::get('/approved-request', 'approvedRequest')->name('approvedRequest');
        Route::post('/statusUpdates', 'statusUpdates')->name('statusUpdates');
    });

    /*
|--------------------------------------------------------------------------
| Booking Requests Routes
|--------------------------------------------------------------------------
*/
    Route::controller(BookingController::class)->group(function () {

        // booking requests lists
        Route::get('/all-bookings', 'allBookings')->name('allBookings');
        Route::get('/pending-bookings', 'pendingBooking')->name('pendingBooking');
        Route::get('/start-bookings', 'startBooking')->name('startBooking');
        Route::get('/complete-bookings', 'endBooking')->name('endBooking');
        Route::get('/cancel-bookings', 'cancelBooking')->name('cancelBooking');
        Route::get('/booking-chat/{status}/{user_id}/{provider_id}', 'chatBetweenBooking')->name('booking.chat');
        Route::get('/booking-detail/{booking_id}', 'bookingDetail')->name('booking.detail');

        // update status
        Route::post('/bookingStatusUpdate', 'bookingStatusUpdate')->name('bookingStatusUpdate');
    });

    /*
|--------------------------------------------------------------------------
| Tax Routes
|--------------------------------------------------------------------------
*/
    Route::controller(TaxController::class)->group(function () {

        Route::get('/tax', 'index')->name('tax.index');
        Route::post('/tax', 'store')->name('tax.store');
    });
    Route::resource('commission', CommissionController::class);

    Route::get('/get-subcategories/{id}', [CommissionController::class, 'getSubCategories']);

    /*
|--------------------------------------------------------------------------
| Shopkeepers & Shops Routes
|--------------------------------------------------------------------------
*/
    Route::controller(ShopController::class)->group(function () {

        // shopkeeper shopkeepers
        Route::get('/all-shopkeepers', 'shopkeepers')->name('shopkeepers');
        Route::delete('/shopkeepers/{id}', 'destroy')->name('shopkeepers.destroy');
        Route::post('/status/update', 'statusUpdate')->name('shopkeepers.status');
        Route::get('/shopkeeper/{id}', 'shops')->name('shops');
    });

    /*
|--------------------------------------------------------------------------
| Setting Routes
|--------------------------------------------------------------------------
*/
    Route::controller(SettingController::class)->group(function () {
        Route::get('/appUrl', 'appUrl')->name('appUrl.index');
        Route::post('/appUrl/store', 'appUrlStore')->name('appUrl.store');
        Route::post('app/is', 'appIsOn')->name('settings.appIsOn');
        Route::delete('/appUrl/destroy/{id}', 'appUrlDestroy')->name('appUrl.destroy');
    });
});
