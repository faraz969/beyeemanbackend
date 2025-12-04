<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendorAuthController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VendorDashboardController;
use App\Http\Controllers\Api\VendorProfileController;
use App\Http\Controllers\Api\VendorAvailabilityRequestController;
use App\Http\Controllers\Api\VendorOrderController;
use App\Http\Controllers\Api\CustomerProfileController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerHomeController;
use App\Http\Controllers\Api\CustomerProductController;
use App\Http\Controllers\Api\AvailabilityRequestController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\CustomerAddressController;
use App\Http\Controllers\Api\FeeSettingsController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CustomerShopController;
use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Vendor Auth Routes
Route::prefix('vendor')->group(function () {
    Route::post('/send-otp', [VendorAuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [VendorAuthController::class, 'verifyOtp']);
    Route::post('/register', [VendorAuthController::class, 'register']);
    Route::post('/login', [VendorAuthController::class, 'login']);
});

// Protected Vendor Routes
Route::middleware('auth:sanctum')->prefix('vendor')->group(function () {
    // Shop Routes
    Route::post('/shop', [ShopController::class, 'store']);
    Route::get('/shop', [ShopController::class, 'show']);
    Route::post('/shop/delivery-zone', [ShopController::class, 'addDeliveryZone']);
    Route::put('/shop/delivery-zone/{id}', [ShopController::class, 'updateDeliveryZone']);
    Route::delete('/shop/delivery-zone/{id}', [ShopController::class, 'deleteDeliveryZone']);
    Route::post('/shop/payment-details', [ShopController::class, 'savePaymentDetails']);
    
    // Subscription Routes
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::get('/subscription/current', [SubscriptionController::class, 'current']);
    
    // Payment Routes
    Route::post('/payment/initialize-subscription', [PaymentController::class, 'initializeSubscriptionPayment']);
    Route::post('/payment/verify', [PaymentController::class, 'verifyPayment']);
    
    // Product Routes
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    
    // Dashboard Routes
    Route::get('/dashboard/stats', [VendorDashboardController::class, 'stats']);
    
    // Availability Request Routes
    Route::get('/availability-requests', [VendorAvailabilityRequestController::class, 'index']);
    Route::get('/availability-requests/{id}', [VendorAvailabilityRequestController::class, 'show']);
    Route::put('/availability-requests/{id}', [VendorAvailabilityRequestController::class, 'update']);
    
    // Order Routes
    Route::get('/orders', [VendorOrderController::class, 'index']);
    Route::get('/orders/{id}', [VendorOrderController::class, 'show']);
    Route::put('/orders/{id}', [VendorOrderController::class, 'update']);
    
    // Profile Routes
    Route::get('/profile', [VendorProfileController::class, 'show']);
    Route::post('/profile', [VendorProfileController::class, 'update']);
    Route::post('/profile/change-password', [VendorProfileController::class, 'changePassword']);
    
    // Dispute Routes
    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::post('/disputes', [DisputeController::class, 'store']);
    Route::get('/disputes/{id}', [DisputeController::class, 'show']);
    
    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
});

// Customer Auth Routes
Route::prefix('customer')->group(function () {
    Route::post('/send-otp', [CustomerAuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOtp']);
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);
});

// Public Customer Routes
Route::get('/home', [CustomerHomeController::class, 'index']);
Route::get('/search', [CustomerHomeController::class, 'search']);
Route::get('/products/{id}', [CustomerProductController::class, 'show']);
Route::get('/shops/{id}', [CustomerShopController::class, 'show']);
Route::get('/shops/{id}/delivery-fee', [CustomerShopController::class, 'getDeliveryFee']);
Route::get('/categories/{id}/shops', [CustomerHomeController::class, 'shopsByCategory']);
Route::get('/vendors/{id}/reviews', [ReviewController::class, 'vendorReviews']);
Route::get('/categories', [CustomerHomeController::class, 'categories']);
Route::get('/fee-settings', [FeeSettingsController::class, 'index']);

// Protected Customer Routes
Route::middleware('auth:sanctum')->prefix('customer')->group(function () {
    // Address Routes
    Route::get('/addresses', [CustomerAddressController::class, 'index']);
    Route::post('/addresses', [CustomerAddressController::class, 'store']);
    Route::put('/addresses/{id}', [CustomerAddressController::class, 'update']);
    Route::delete('/addresses/{id}', [CustomerAddressController::class, 'destroy']);
    
    // Cart Routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    
    // Availability Request Routes
    Route::get('/availability-requests', [AvailabilityRequestController::class, 'index']);
    Route::post('/availability-requests', [AvailabilityRequestController::class, 'store']);
    Route::get('/availability-requests/{id}', [AvailabilityRequestController::class, 'show']);
    
    // Order Routes
    Route::get('/orders', [CustomerOrderController::class, 'index']);
    Route::post('/orders', [CustomerOrderController::class, 'store']);
    Route::get('/orders/{id}', [CustomerOrderController::class, 'show']);
    Route::post('/orders/{id}/confirm-delivery', [CustomerOrderController::class, 'confirmDelivery']);
    
    // Payment Routes
    Route::post('/payment/initialize-order', [PaymentController::class, 'initializeOrderPayment']);
    Route::post('/payment/verify-order', [PaymentController::class, 'verifyOrderPayment']);
    
    // Review Routes
    Route::post('/reviews', [ReviewController::class, 'store']);
    
    // Profile Routes
    Route::get('/profile', [CustomerProfileController::class, 'show']);
    Route::post('/profile', [CustomerProfileController::class, 'update']);
    Route::post('/profile/change-password', [CustomerProfileController::class, 'changePassword']);
    
    // Dispute Routes
    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::post('/disputes', [DisputeController::class, 'store']);
    Route::get('/disputes/{id}', [DisputeController::class, 'show']);
    
    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
});
