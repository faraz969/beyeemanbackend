<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaystackService;
use App\Models\SubscriptionPackage;
use App\Models\VendorSubscription;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->middleware('auth:sanctum');
        $this->paystackService = $paystackService;
    }

    /**
     * Initialize Paystack payment for subscription
     */
    public function initializeSubscriptionPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_package_id' => 'required|exists:subscription_packages,id',
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

        // Get vendor email - prefer business_email, fallback to user email
        $user = $request->user();
        $email = $vendor->business_email ?? $user->email ?? 'vendor@example.com';

        // Convert price to kobo (Paystack uses smallest currency unit)
        // Assuming price is in base currency, multiply by 100 for kobo
        $amountInKobo = (int)($package->price * 100);

        // Prepare metadata
        $metadata = [
            'vendor_id' => $vendor->id,
            'subscription_package_id' => $package->id,
            'package_name' => $package->name,
            'custom_fields' => [
                [
                    'display_name' => 'Vendor',
                    'variable_name' => 'vendor_name',
                    'value' => $vendor->full_name,
                ],
                [
                    'display_name' => 'Package',
                    'variable_name' => 'package_name',
                    'value' => $package->name,
                ],
            ],
        ];

        // Initialize transaction with Paystack
        $result = $this->paystackService->initializeTransaction($email, $amountInKobo, $metadata);

        if ($result['success']) {
            // Create pending subscription record
            $startsAt = Carbon::now();
            $expiresAt = $startsAt->copy();

            switch ($package->duration_type) {
                case 'month':
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

            // Create subscription with pending status
            // Store reference in a temporary way - we'll use a transaction reference field if needed
            $subscription = VendorSubscription::create([
                'vendor_id' => $vendor->id,
                'subscription_package_id' => $package->id,
                'payment_method' => 'bank_card', // Paystack is card payment
                'payment_status' => 'pending',
                'status' => 'cancelled', // Will be updated to 'active' after payment verification
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initialized successfully',
                'data' => [
                    'access_code' => $result['data']['access_code'],
                    'authorization_url' => $result['data']['authorization_url'],
                    'reference' => $result['data']['reference'],
                    'public_key' => $this->paystackService->getPublicKey(),
                    'subscription_id' => $subscription->id,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to initialize payment',
        ], 400);
    }

    /**
     * Verify Paystack payment and activate subscription
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
            'subscription_id' => 'required|exists:vendor_subscriptions,id',
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

        $subscription = VendorSubscription::where('id', $request->subscription_id)
            ->where('vendor_id', $vendor->id)
            ->firstOrFail();

        // Verify transaction with Paystack
        $result = $this->paystackService->verifyTransaction($request->reference);

        if ($result['success'] && $result['status']) {
            // Payment successful - activate subscription
            $subscription->update([
                'payment_status' => 'completed',
                'status' => 'active',
            ]);

            // Activate vendor
            $vendor->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and subscription activated successfully',
                'data' => $subscription->load('package'),
            ]);
        }

        // Payment failed
        $subscription->update([
            'payment_status' => 'failed',
            'status' => 'cancelled',
        ]);

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Payment verification failed',
        ], 400);
    }

    /**
     * Initialize Paystack payment for customer order
     */
    public function initializeOrderPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::findOrFail($request->order_id);
        
        // Verify order belongs to authenticated customer
        if ($order->customer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to order',
            ], 403);
        }

        // Check if order already has payment reference
        if ($order->paystack_reference) {
            // Return existing payment details
            $paystackService = app(\App\Services\PaystackService::class);
            return response()->json([
                'success' => true,
                'message' => 'Payment already initialized',
                'data' => [
                    'reference' => $order->paystack_reference,
                    'public_key' => $paystackService->getPublicKey(),
                    'order_id' => $order->id,
                ],
            ]);
        }

        $user = $request->user();
        $email = $user->email ?? 'customer@example.com';

        // Convert total amount to kobo (Paystack uses smallest currency unit)
        $amountInKobo = (int)($order->total_amount * 100);

        // Prepare metadata
        $metadata = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_id' => $user->id,
            'shop_id' => $order->shop_id,
            'custom_fields' => [
                [
                    'display_name' => 'Order Number',
                    'variable_name' => 'order_number',
                    'value' => $order->order_number,
                ],
                [
                    'display_name' => 'Customer',
                    'variable_name' => 'customer_name',
                    'value' => $user->name,
                ],
            ],
        ];

        // Initialize transaction with Paystack
        $result = $this->paystackService->initializeTransaction($email, $amountInKobo, $metadata);

        if ($result['success']) {
            // Store Paystack reference in order
            $order->paystack_reference = $result['data']['reference'];
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment initialized successfully',
                'data' => [
                    'access_code' => $result['data']['access_code'],
                    'authorization_url' => $result['data']['authorization_url'],
                    'reference' => $result['data']['reference'],
                    'public_key' => $this->paystackService->getPublicKey(),
                    'order_id' => $order->id,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to initialize payment',
        ], 400);
    }

    /**
     * Verify Paystack payment for customer order
     */
    public function verifyOrderPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::findOrFail($request->order_id);
        
        // Verify order belongs to authenticated customer
        if ($order->customer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to order',
            ], 403);
        }

        // Verify transaction with Paystack
        $result = $this->paystackService->verifyTransaction($request->reference);

        if ($result['success'] && $result['status']) {
            // Extract authorization code from transaction data (for refunds/disputes)
            $authorizationCode = null;
            if (isset($result['data']['authorization'])) {
                $authorizationCode = $result['data']['authorization']['authorization_code'] ?? null;
            }

            // Payment successful - update order
            $order->update([
                'payment_status' => 'full',
                'paystack_reference' => $request->reference,
                'authorization_code' => $authorizationCode,
            ]);

            // Clear cart items now that payment is confirmed
            \App\Models\Cart::where('customer_id', $request->user()->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and order confirmed successfully',
                'data' => $order->load('items', 'shop', 'deliveryAddress'),
            ]);
        }

        // Payment failed
        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Payment verification failed',
        ], 400);
    }
}
