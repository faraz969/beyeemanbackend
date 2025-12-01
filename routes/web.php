<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Vendor Management Routes
    Route::get('/vendors', [App\Http\Controllers\Admin\VendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/{id}', [App\Http\Controllers\Admin\VendorController::class, 'show'])->name('vendors.show');
    Route::post('/vendors/{id}/status', [App\Http\Controllers\Admin\VendorController::class, 'updateStatus'])->name('vendors.update-status');
    Route::post('/vendors/{vendorId}/shop-status', [App\Http\Controllers\Admin\VendorController::class, 'updateShopStatus'])->name('vendors.update-shop-status');
    
    // Product Management Routes
    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'show'])->name('products.show');
    Route::post('/products/{id}/status', [App\Http\Controllers\Admin\ProductController::class, 'updateStatus'])->name('products.update-status');
    
    // Order Management Routes
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    
    // Customer Management Routes
    Route::get('/customers', [App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{id}', [App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');
    
    // Category Management Routes
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class)->except(['show']);
    Route::get('/categories/{id}', [App\Http\Controllers\Admin\CategoryController::class, 'show'])->name('categories.show');
    
    // Role Management Routes
    Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
    
    // Permission Management Routes
    Route::resource('permissions', App\Http\Controllers\Admin\PermissionController::class);
    
    // Banner Management Routes
    Route::resource('banners', App\Http\Controllers\Admin\BannerController::class);
    
    // Dispute Management Routes
    Route::get('/disputes', [App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{id}', [App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('disputes.show');
    Route::put('/disputes/{id}', [App\Http\Controllers\Admin\DisputeController::class, 'update'])->name('disputes.update');
    
    // Subscription Package Management Routes
    Route::resource('subscription-packages', App\Http\Controllers\Admin\SubscriptionPackageController::class);
    
    // Notification Management Routes
    Route::resource('notifications', App\Http\Controllers\Admin\NotificationController::class);
    
    // Fee Settings Routes
    Route::get('/fee-settings', [App\Http\Controllers\Admin\FeeSettingController::class, 'edit'])->name('fee-settings.edit');
    Route::put('/fee-settings', [App\Http\Controllers\Admin\FeeSettingController::class, 'update'])->name('fee-settings.update');
    
    // Transfer Management Routes
    Route::get('/transfers', [App\Http\Controllers\Admin\TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/{id}', [App\Http\Controllers\Admin\TransferController::class, 'show'])->name('transfers.show');
    Route::post('/transfers/{id}/verify', [App\Http\Controllers\Admin\TransferController::class, 'verify'])->name('transfers.verify');
});

require __DIR__.'/auth.php';
