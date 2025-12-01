<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use App\Models\VendorSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Get all subscription packages
    public function index()
    {
        $packages = SubscriptionPackage::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    // Subscribe to a package (deprecated - use Paystack payment flow instead)
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_package_id' => 'required|exists:subscription_packages,id',
            'payment_method' => 'required|in:momo,bank_card,wallet',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $package = SubscriptionPackage::findOrFail($request->subscription_package_id);

        // Calculate expiry date
        $startsAt = Carbon::now();
        $expiresAt = $startsAt->copy();

        switch ($package->duration_type) {
            case 'month':
                $expiresAt->addMonths($package->duration_value);
                break;
            case 'months':
                $expiresAt->addMonths($package->duration_value);
                break;
            case 'year':
                $expiresAt->addYears($package->duration_value);
                break;
            case 'days':
                $expiresAt->addDays($package->duration_value);
                break;
        }

        // Dummy payment processing (always success for now)
        $paymentStatus = 'completed';

        $subscription = VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'subscription_package_id' => $package->id,
            'payment_method' => $request->payment_method,
            'payment_status' => $paymentStatus,
            'status' => 'active',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);

        // Activate vendor
        $vendor->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Subscription activated successfully',
            'data' => $subscription->load('package'),
        ], 201);
    }

    // Get vendor's current subscription
    public function current(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        // Get the most recent subscription (including expired ones for renewal context)
        $subscription = VendorSubscription::where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->with('package')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => true,
                'data' => null,
                'is_active' => false,
                'is_expired' => true,
                'days_until_expiry' => null,
                'needs_renewal' => true,
            ]);
        }

        $now = Carbon::now();
        $expiresAt = Carbon::parse($subscription->expires_at);
        $isActive = $expiresAt->isFuture();
        $isExpired = $expiresAt->isPast();
        $daysUntilExpiry = $isActive ? $now->diffInDays($expiresAt, false) : null;
        
        // Check if subscription is expiring within 5 days or is expired
        $needsRenewal = $isExpired || ($isActive && $daysUntilExpiry !== null && $daysUntilExpiry <= 5);

        return response()->json([
            'success' => true,
            'data' => $subscription,
            'is_active' => $isActive,
            'is_expired' => $isExpired,
            'days_until_expiry' => $daysUntilExpiry,
            'expires_at' => $subscription->expires_at,
            'needs_renewal' => $needsRenewal,
        ]);
    }
}

