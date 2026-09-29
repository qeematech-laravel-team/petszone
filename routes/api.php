<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderRefundController;
use App\Http\Controllers\Api\FawryWebhookController;
use App\Http\Controllers\Api\GeideaWebhookController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes with rate limiting
Route::post('/auth/register', [AuthController::class, 'register'])
    // ->middleware('throttle:5,1')
    ->name('api.register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('api.login'); // Rate limiting handled in LoginRequest
Route::post('/auth/send-login-otp', [AuthController::class, 'sendLoginCode']); // Rate limiting handled in LoginRequest
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail'])
    // ->middleware('throttle:10,1')
    ->name('api.verify-email');
Route::post('/auth/verify-phone', [AuthController::class, 'verifyPhone'])
    // ->middleware('throttle:10,1')
    ->name('api.verify-phone');
Route::post('/auth/resend-verification-code', [AuthController::class, 'resendVerificationCode'])
    // ->middleware('throttle:3,1')
    ->name('api.resend-verification-code');

Route::post('/auth/reset-password/send-code', [ResetPasswordController::class, 'resetPasswordSendCode'])
    // ->middleware('throttle:5,1')
    ->name('api.reset-password.send-code');
Route::post('/auth/reset-password/verify-code', [ResetPasswordController::class, 'resetPasswordVerifyCode'])
    // ->middleware('throttle:10,1')
    ->name('api.reset-password.verify-code');
Route::post('/auth/reset-password/set-new-password', [ResetPasswordController::class, 'resetPasswordSetNewPassword'])
    // ->middleware('throttle:5,1')
    ->name('api.reset-password.set-new-password');

// User
Route::group(['middleware' => 'locale'], function () {

    Route::any('payments/fawry/webhook', [FawryWebhookController::class, 'handle'])->name('api.payments.fawry.webhook');
    Route::any('payments/geidea/webhook', [GeideaWebhookController::class, 'handle'])->name('api.payments.geidea.webhook');

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {

        # notifications
        Route::get('/notifications', [AuthController::class, 'notifications']);
        Route::get('/notifications/{id}/read', [AuthController::class, 'markAsRead']);
        Route::get('/notifications/read-all', [AuthController::class, 'markAllAsRead']);
        # fcm token
        Route::post('/update-fcm-token', [AuthController::class, 'updateFcmToken']);


        // Authentication routes
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('api.user');

        // Profile routes with rate limiting
        Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])
            // ->middleware('throttle:10,1')
            ->name('api.profile.update');

        // Password routes with rate limiting
        Route::put('/password', [PasswordController::class, 'update'])
            // ->middleware('throttle:5,1')
            ->name('api.password.update');

        // addresses with rate limiting
        Route::get('addresses', [AddressController::class, 'index'])->name('api.addresses.index');
        Route::post('addresses', [AddressController::class, 'store'])
            // ->middleware('throttle:10,1')
            ->name('api.addresses.store');
        Route::delete('addresses/{address}', [AddressController::class, 'destroy'])
            // ->middleware('throttle:10,1')
            ->name('api.addresses.destroy');

        // favorite-list
        Route::get('favorite-list', [ProductController::class, 'favoriteList']);

        // toggle-favorite
        Route::post('products/{product}/toggle-favorite', [ProductController::class, 'toggleFavorite'])->name('api.products.toggle-favorite');

        // Tickets routes with rate limiting
        Route::get('tickets', [\App\Http\Controllers\Api\TicketController::class, 'index'])->name('api.tickets.index');
        Route::post('tickets', [\App\Http\Controllers\Api\TicketController::class, 'store'])
            // ->middleware('throttle:10,1')
            ->name('api.tickets.store');
        Route::get('tickets/{ticket}', [\App\Http\Controllers\Api\TicketController::class, 'show'])->name('api.tickets.show');
        Route::put('tickets/{ticket}', [\App\Http\Controllers\Api\TicketController::class, 'update'])
            // ->middleware('throttle:10,1')
            ->name('api.tickets.update');
        Route::delete('tickets/{ticket}', [\App\Http\Controllers\Api\TicketController::class, 'destroy'])
            // ->middleware('throttle:5,1')
            ->name('api.tickets.destroy');
        Route::post('tickets/{ticket}/add-message', [\App\Http\Controllers\Api\TicketController::class, 'addMessage'])
            // ->middleware('throttle:20,1')
            ->name('api.tickets.add-message');
        Route::post('tickets/{ticket}/update-status', [\App\Http\Controllers\Api\TicketController::class, 'updateStatus'])
            // ->middleware('throttle:10,1')
            ->name('api.tickets.update-status');

        // cart with rate limiting
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart/{product}', [CartController::class, 'add']);
            // ->middleware('throttle:30,1');
        Route::put('cart/{product}', [CartController::class, 'updateQuantity']);
            // ->middleware('throttle:30,1');
        Route::delete('cart/{product}', [CartController::class, 'remove']);
            // ->middleware('throttle:30,1');
        Route::delete('cart', [CartController::class, 'clear']);
            // ->middleware('throttle:10,1');
        Route::post('cart/apply-coupon', [CartController::class, 'applyCoupon'])
            // ->middleware('throttle:10,1')
            ->name('api.cart.apply-coupon');

        // orders (user) with rate limiting
        Route::apiResource('orders', OrderController::class)->only(['index', 'show']);
        Route::post('orders', [OrderController::class, 'store'])
            // ->middleware('throttle:10,1')
            ->name('api.orders.store');
        Route::post('orders/calculate-shipping', [OrderController::class, 'calculateShipping'])
            // ->middleware('throttle:30,1')
            ->name('api.orders.calculate-shipping');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])
            // ->middleware('throttle:5,1')
            ->name('api.orders.cancel');
        Route::post('orders/{order}/reorder', [OrderController::class, 'reorder'])
            // ->middleware('throttle:10,1')
            ->name('api.orders.reorder');
        Route::post('orders/{order}/pay', [OrderController::class, 'pay'])
            // ->middleware('throttle:10,1')
            ->name('api.orders.pay');
        Route::post('orders/{order}/refund-request', [OrderRefundController::class, 'store'])
            // ->middleware('throttle:5,1')
            ->name('api.orders.refund-request');

        // transactions (user)
        Route::get('wallet/history', [\App\Http\Controllers\Api\TransactionController::class, 'walletHistory'])->name('api.wallet.history');
        Route::get('points/history', [\App\Http\Controllers\Api\TransactionController::class, 'pointHistory'])->name('api.points.history');

        // Ratings with rate limiting
        Route::post('products/{product}/rate', [RatingController::class, 'rateProduct'])
            // ->middleware('throttle:10,1')
            ->name('api.products.rate');
        Route::post('vendors/{vendor}/rate', [RatingController::class, 'rateVendor'])
            // ->middleware('throttle:10,1')
            ->name('api.vendors.rate');

        // Reports with rate limiting
        Route::post('products/{product}/report', [ReportController::class, 'reportProduct'])
            // ->middleware('throttle:5,1')
            ->name('api.products.report');
        Route::post('vendors/{vendor}/report', [ReportController::class, 'reportVendor'])
            // ->middleware('throttle:5,1')
            ->name('api.vendors.report');
    });

    // Category routes
    Route::apiResource('/categories', CategoryController::class)->only(['index', 'show']);

    // Vendor routes
    Route::apiResource('/vendors', VendorController::class)->only(['index', 'show']);

    // Product routes
    Route::apiResource('/products', ProductController::class)->only(['index', 'show']);

    // Slider routes
    Route::apiResource('/sliders', SliderController::class)->only(['index']);
});
