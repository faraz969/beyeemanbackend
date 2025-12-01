<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\VendorWallet;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function stats(Request $request)
    {
        $vendor = $request->user()->vendor;
        $shop = $vendor->shop;

        // Check subscription even if no shop
        $subscription = $vendor->subscriptions()
            ->where('status', 'active')
            ->with('package')
            ->latest()
            ->first();

        $subscriptionStatus = 'none';
        $subscriptionExpiresAt = null;
        $daysUntilExpiry = null;
        $isExpired = true;
        $needsRenewal = false;

        if ($subscription) {
            $subscriptionStatus = $subscription->package->name;
            $subscriptionExpiresAt = $subscription->expires_at;
            $expiresAt = \Carbon\Carbon::parse($subscription->expires_at);
            $now = now();
            
            $isExpired = $expiresAt->isPast();
            $daysUntilExpiry = $isExpired ? null : $now->diffInDays($expiresAt, false);
            $needsRenewal = $isExpired || ($daysUntilExpiry !== null && $daysUntilExpiry <= 5);
        }

        if (!$shop) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_sales' => 0,
                    'orders_pending' => 0,
                    'products_listed' => 0,
                    'wallet_balance' => 0,
                    'subscription_status' => $subscriptionStatus,
                    'subscription_expires_at' => $subscriptionExpiresAt,
                    'days_until_expiry' => $daysUntilExpiry,
                    'subscription_is_expired' => $isExpired,
                    'subscription_needs_renewal' => $needsRenewal,
                ],
            ]);
        }

        $totalSales = Order::where('shop_id', $shop->id)
            ->where('payment_status', 'full')
            ->sum('total_amount');

        $ordersPending = Order::where('shop_id', $shop->id)
            ->whereIn('order_status', ['pending', 'confirmed', 'processing'])
            ->count();

        $productsListed = Product::where('shop_id', $shop->id)
            ->where('status', 'active')
            ->count();

        $wallet = VendorWallet::where('vendor_id', $vendor->id)
            ->where('payment_type', 'internal_wallet')
            ->first();

        $walletBalance = $wallet ? $wallet->balance : 0;

        // Subscription info is already calculated above (before shop check)

        return response()->json([
            'success' => true,
            'data' => [
                'total_sales' => (float) $totalSales,
                'orders_pending' => $ordersPending,
                'products_listed' => $productsListed,
                'wallet_balance' => (float) $walletBalance,
                'subscription_status' => $subscriptionStatus,
                'subscription_expires_at' => $subscriptionExpiresAt,
                'days_until_expiry' => $daysUntilExpiry,
                'subscription_is_expired' => $isExpired,
                'subscription_needs_renewal' => $needsRenewal,
            ],
        ]);
    }
}

