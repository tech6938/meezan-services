<?php

use App\Http\Controllers\AdminAccessController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\MainCategoryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UserProviderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

// Route::get('/clear', function () {
//     \Artisan::call('cache:clear');
//     \Artisan::call('config:clear');
//     \Artisan::call('route:clear');
//     \Artisan::call('view:clear');

//     return "Cleared!";
// });

Route::controller(AuthController::class)->group(function () {

    Route::get('/', 'login')->name('login');

    Route::post('/match-login', 'match_login')->name('match-login');

    Route::get('/signup', 'signup')->name('signup');
    Route::post('/insert-signup', 'insert_signup')->name('insert-signup');

    Route::post('/logout', 'logout')->name('logout');

    Route::get('/forget', 'forget')->name('forget');
    Route::post('/forget-message', 'forget_message')->name('forget_message');

    Route::get('/otp', 'otp')->name('otp');
    Route::post('/matching-route', 'matching_route')->name('matching_route');

    Route::get('/reset', 'reset')->name('reset');
    Route::post('/reset-password', 'update_password')->name('reset_password');
});

Route::middleware(['admin.auth', 'admin.permission'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    Route::resource('/main-categories', MainCategoryController::class);
    Route::resource('/sub-categories', SubCategoryController::class);

    Route::prefix('admin')->group(function () {
        Route::controller(ChatsController::class)->group(function () {
            Route::get('/chat-list', 'chatsList')->name('chatsList');
            Route::get('/chats/{sender_type}/{sender_id}/{receiver_type}/{receiver_id}/{booking_id}', 'chatBetween')->name('chats.between');
            Route::post('/chats/export', 'exportSelectedChats')->name('chats.export');
        });

        Route::get('/chats/delete/{sender_type}/{sender_id}/{receiver_type}/{receiver_id}', [ChatsController::class, 'deleteChatPage'])->name('chats.delete.page');
        Route::post('/chats/delete-by-booking', [ChatsController::class, 'deleteChatByBooking'])->name('chats.delete.by-booking');
        Route::post('/chats/force-delete', [ChatsController::class, 'forceDeleteConversation'])->name('chats.force-delete');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::delete('/chats/delete-by-order/{orderNo}', [ChatsController::class, 'deleteChatsByOrderNo'])->name('chats.deleteByOrderNo.legacy');
    });

    Route::controller(UserProviderController::class)->group(function () {
        Route::get('/users-list', 'userList')->name('userList');
        Route::get('/view-user-detail/{id}', 'viewUserDetail')->name('viewUserDetail');
        Route::post('/updateUserStatus', 'updateUserStatus')->name('updateUserStatus');
        Route::delete('/user/{id}', 'userDestroy')->name('user.destroy');

        Route::get('/users/export', 'exportUsers')->name('users.export');
        Route::get('/users/preview', 'previewUsers')->name('users.preview');

        Route::get('/approved-providers', 'approvedProviders')->name('approvedProviders');
        Route::get('/blocked-providers', 'blockedProviders')->name('blockedProviders');
        Route::get('/suspended-providers', 'suspendedProviders')->name('suspendedProviders');
        Route::get('/pending-providers', 'pendingProviders')->name('pendingProviders');
        Route::get('/all-providers', 'allProviders')->name('allProviders');
        Route::get('/provider/details/{id}', 'viewProviderDetail')->name('provider.details');
        Route::post('/statusUpdate', 'statusUpdate')->name('statusUpdate');
        Route::delete('/provider/{id}', 'destroy')->name('provider.destroy');
        Route::get('/providers/export', 'exportProviders')->name('providers.export');
        Route::get('/providers/preview', 'previewProviders')->name('providers.preview');
        Route::get('/export-providers-multi', 'exportProvidersMultiSheet')->name('providers.exportMultiSheet');
        Route::get('/users/export-multi', 'exportUsersMultiSheet')->name('users.exportMulti');
    });

    // routes/web.php

    Route::controller(ServiceRequestController::class)->group(function () {
        // Main routes
        Route::get('/orders', 'allRequest')->name('allRequest');
        Route::get('/pending-request', 'pendingOrders')->name('pendingRequest');
        Route::get('/approved-request', 'acceptedOrders')->name('approvedRequest');

        // Order views with filters
        Route::get('/orders/pending-orders', function (Request $request) {
            return app(ServiceRequestController::class)->ordersView($request, 'pending_orders');
        })->name('pendingOrders');

        Route::get('/orders/accept-orders', function (Request $request) {
            return app(ServiceRequestController::class)->ordersView($request, 'accept_orders');
        })->name('acceptOrders');

        Route::get('/orders/accepted-orders', function (Request $request) {
            return app(ServiceRequestController::class)->ordersView($request, 'accepted_orders');
        })->name('acceptedOrders');

        Route::get('/orders/assigned-orders', function (Request $request) {
            return app(ServiceRequestController::class)->ordersView($request, 'assigned_orders');
        })->name('assignedOrders');

        Route::get('/orders/cancelled-orders', function (Request $request) {
            return app(ServiceRequestController::class)->ordersView($request, 'cancelled_orders');
        })->name('cancelledOrders');

        Route::get('/orders/pending-bookings', function (Request $request) {
            return app(ServiceRequestController::class)->ordersView($request, 'pending_bookings');
        })->name('pendingBookings');

        Route::get('/orders/completed-orders', function (Request $request) {
            return app(ServiceRequestController::class)->ordersView($request, 'completed_orders');
        })->name('completedOrders');

        // Other routes
        Route::post('/statusUpdates', 'statusUpdates')->name('statusUpdates');
        Route::get('/orders/preview', 'previewRequests')->name('orders.preview');
        Route::get('/order/details/{id}', 'getAcceptedProviders')->name('service-request.accepted-providers');
        Route::get('/orders/export', 'exportRequests')->name('requests.export');
        Route::get('/orders/export-multi', 'exportRequestsMultiSheet')->name('requests.exportMulti');
    });

    Route::controller(BookingController::class)->group(function () {
        Route::get('/all-bookings', 'allBookings')->name('allBookings');
        Route::get('/pending-bookings', 'pendingBooking')->name('pendingBooking');
        Route::get('/start-bookings', 'startBooking')->name('startBooking');
        Route::get('/complete-bookings', 'endBooking')->name('endBooking');
        Route::get('/cancel-bookings', 'cancelBooking')->name('cancelBooking');
        Route::get('/booking-chat/{status}/{user_id}/{provider_id}', 'chatBetweenBooking')->name('booking.chat');
        Route::get('/booking-detail/{booking_id}', 'bookingDetail')->name('booking.detail');
        Route::post('/bookingStatusUpdate', 'bookingStatusUpdate')->name('bookingStatusUpdate');
        Route::get('/bookings/export', 'exportBookings')->name('bookings.export');
        Route::get('/bookings/export-multi', 'exportBookingsMultiSheet')->name('bookings.exportMultiMulti');
        Route::get('/bookings/preview', 'previewBooking')->name('bookings.preview');
    });

    Route::controller(TaxController::class)->group(function () {
        Route::get('/tax', 'index')->name('tax.index');
        Route::post('/tax', 'store')->name('tax.store');
    });

    Route::resource('commission', CommissionController::class);
    Route::get('/get-subcategories/{id}', [CommissionController::class, 'getSubCategories']);

    Route::controller(ShopController::class)->group(function () {
        Route::get('/all-shopkeepers', 'shopkeepers')->name('shopkeepers');
        Route::delete('/shopkeepers/{id}', 'destroy')->name('shopkeepers.destroy');
        Route::post('/status/update', 'statusUpdate')->name('shopkeepers.status');
        Route::get('/shopkeeper/{id}', 'shops')->name('shops');
    });

    Route::controller(SettingController::class)->group(function () {
        Route::get('/appUrl', 'appUrl')->name('appUrl.index');
        Route::post('/appUrl/store', 'appUrlStore')->name('appUrl.store');
        Route::post('/settings/user-app-status', 'userAppIsOn')->name('settings.userAppIsOn');
        Route::post('/settings/provider-app-status', 'providerAppIsOn')->name('settings.providerAppIsOn');
        Route::delete('/appUrl/destroy/{id}', 'appUrlDestroy')->name('appUrl.destroy');
        Route::get('/privacyPolicy/partner', 'privacyPolicy')->name('privacyPolicy.provider');
        Route::get('/terms&conditions/partner', 'termsConditions')->name('termsConditions.provider');
        Route::get('/privacyPolicy/customer', 'privacyCustomer')->name('privacyPolicy.customer');
        Route::get('/terms&conditions/customer', 'termsConditionsCustomer')->name('termsConditions.customer');
        Route::get('/partner/agreement', 'partnerAgreement')->name('partnerAgreement');
        Route::get('/about_us', 'aboutUs')->name('aboutUs');
        Route::get('/contact_us', 'contactUs')->name('contactUs');
    });

    Route::prefix('referral')->name('referrals.')->controller(ReferralController::class)->group(function () {
        Route::get('/settings', 'settings')->name('settings');
        Route::post('/settings', 'updateSettings')->name('settings.update');
        Route::get('/tree', 'tree')->name('tree');
        Route::get('/customer-earnings', 'customerEarnings')->name('customerEarnings');
        Route::get('/commission-logs', 'commissionLogs')->name('commissionLogs');
        Route::get('/reports', 'reports')->name('reports');
    });

    Route::controller(PageController::class)->group(function () {
        Route::get('/pages', 'index')->name('pages.index');
        Route::get('/pages/content/{pageId}', 'getPageContent')->name('pages.content');
    });

    Route::resource('admin', AdminController::class);

    Route::controller(AdminAccessController::class)->prefix('access-control')->name('access-control.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{role}/edit', 'edit')->name('edit');
        Route::put('/{role}', 'update')->name('update');
        Route::delete('/{role}', 'destroy')->name('destroy');
    });
});

Route::get('/referral/{code}', [ReferralController::class, 'landing'])->name('referral.landing');
