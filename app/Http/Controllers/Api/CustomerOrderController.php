<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use App\Models\AvailabilityRequest;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Create order (checkout)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'delivery_address_id' => 'required|exists:customer_addresses,id',
            'payment_method' => 'required|in:paystack',
            'cart_item_ids' => 'required|array',
            'cart_item_ids.*' => 'exists:carts,id',
            'customer_notes' => 'nullable|string',
            'coupon_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get cart items
        $cartItems = Cart::with('product')
            ->whereIn('id', $request->cart_item_ids)
            ->where('customer_id', $request->user()->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No items in cart',
            ], 400);
        }

        // Check availability for all products (optional - for information only)
        // Note: Availability check is optional. Orders can be placed even if availability is not confirmed.
        $availabilityConfirmed = true;
        $unavailableProducts = [];
        
        foreach ($cartItems as $cartItem) {
            $availabilityRequest = AvailabilityRequest::where('customer_id', $request->user()->id)
                ->where('product_id', $cartItem->product_id)
                ->where('status', 'available')
                ->latest()
                ->first();

            if (!$availabilityRequest) {
                $availabilityConfirmed = false;
                $unavailableProducts[] = $cartItem->product->name;
            }
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $cartItem) {
            $price = $cartItem->product->price;
            $discount = $cartItem->product->discount ?? 0;
            $finalPrice = $price - ($price * $discount / 100);
            $subtotal += $finalPrice * $cartItem->quantity;
        }

        // Get delivery fee (simplified - use first delivery zone)
        $shop = \App\Models\Shop::findOrFail($request->shop_id);
        $deliveryZone = $shop->deliveryZones()->first();
        $deliveryFee = $deliveryZone ? $deliveryZone->delivery_fee : 0;

        $couponDiscount = 0; // TODO: Calculate coupon discount

        // Get fee settings and calculate fees
        $feeSettings = \App\Models\FeeSetting::getSettings();
        
        // Calculate processing fee (charged to customer)
        $processingFee = 0;
        if ($feeSettings->processing_fee_applicable_to === 'customer' || $feeSettings->processing_fee_applicable_to === 'both') {
            if ($feeSettings->processing_fee_type === 'percentage') {
                $processingFee = ($subtotal + $deliveryFee - $couponDiscount) * ($feeSettings->processing_fee_value / 100);
            } else {
                $processingFee = $feeSettings->processing_fee_value;
            }
        }

        // Calculate platform fee (charged to customer if applicable)
        $platformFee = 0;
        if ($feeSettings->platform_fee_applicable_to === 'customer' || $feeSettings->platform_fee_applicable_to === 'both') {
            if ($feeSettings->platform_fee_type === 'percentage') {
                $platformFee = ($subtotal + $deliveryFee - $couponDiscount) * ($feeSettings->platform_fee_value / 100);
            } else {
                $platformFee = $feeSettings->platform_fee_value;
            }
        }

        // Calculate total amount (subtotal + delivery + fees - coupon)
        $totalAmount = $subtotal + $deliveryFee + $processingFee + $platformFee - $couponDiscount;

        // Create order with pending payment
        $order = Order::create([
            'customer_id' => $request->user()->id,
            'shop_id' => $request->shop_id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'delivery_fee' => $deliveryFee,
            'processing_fee' => $processingFee,
            'platform_fee' => $platformFee,
            'delivery_address_id' => $request->delivery_address_id,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending', // Will be updated when payment is verified
            'order_status' => 'pending',
            'delivery_status' => 'pending',
            'customer_notes' => $request->customer_notes,
            'coupon_code' => $request->coupon_code,
            'coupon_discount' => $couponDiscount,
            'availability_confirmed' => $availabilityConfirmed,
            'confirmed_at' => $availabilityConfirmed ? now() : null,
        ]);

        // Create order items
        foreach ($cartItems as $cartItem) {
            $price = $cartItem->product->price;
            $discount = $cartItem->product->discount ?? 0;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $price,
                'discount' => $discount,
                'notes' => $cartItem->notes,
            ]);
        }

        // Log activity
        ActivityLogService::logCreate(
            $order,
            "Order #{$order->order_number} created by customer",
            $request
        );

        // Initialize Paystack payment
        $paystackService = app(\App\Services\PaystackService::class);
        $user = $request->user();
        $email = $user->email ?? 'customer@example.com';

        // Convert total amount to kobo (Paystack uses smallest currency unit)
        $amountInKobo = (int)($totalAmount * 100);

        // Prepare metadata
        $metadata = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_id' => $user->id,
            'shop_id' => $request->shop_id,
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
        $result = $paystackService->initializeTransaction($email, $amountInKobo, $metadata);

        if ($result['success']) {
            // Store Paystack reference in order
            $order->paystack_reference = $result['data']['reference'];
            $order->save();

            // Don't clear cart yet - wait for payment verification
            return response()->json([
                'success' => true,
                'message' => 'Order created. Please complete payment.',
                'data' => [
                    'order' => $order->load('items', 'shop', 'deliveryAddress'),
                    'payment' => [
                        'access_code' => $result['data']['access_code'],
                        'authorization_url' => $result['data']['authorization_url'],
                        'reference' => $result['data']['reference'],
                        'public_key' => $paystackService->getPublicKey(),
                        'order_id' => $order->id,
                    ],
                ],
            ], 201);
        }

        // If Paystack initialization failed, delete order and return error
        $order->delete();

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to initialize payment',
        ], 400);
    }

    // Get customer orders
    public function index(Request $request)
    {
        $orders = Order::with(['shop', 'items.product.images', 'deliveryAddress'])
            ->where('customer_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Format order items with full image URLs
                $order->items->transform(function ($item) {
                    if ($item->product && $item->product->images) {
                        $item->product->images->transform(function ($image) {
                            $image->image_path = asset('storage/' . $image->image_path);
                            return $image;
                        });
                    }
                    return $item;
                });
                return $order;
            });

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    // Get single order
    public function show($id, Request $request)
    {
        $order = Order::with(['shop', 'items.product.images', 'deliveryAddress'])
            ->where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        // Format order items with full image URLs
        $order->items->transform(function ($item) {
            if ($item->product && $item->product->images) {
                $item->product->images->transform(function ($image) {
                    $image->image_path = asset('storage/' . $image->image_path);
                    return $image;
                });
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    // Confirm delivery
    public function confirmDelivery($id, Request $request)
    {
        $order = Order::where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->with('shop.vendor')
            ->firstOrFail();

        // Allow confirmation for orders that are ready, out for delivery, or shipped
        $allowedStatuses = ['out_for_delivery', 'ready', 'processing'];
        if (!in_array($order->delivery_status, $allowedStatuses) && 
            $order->order_status !== 'shipped') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not ready for delivery confirmation',
            ], 400);
        }

        // Update order status
        $oldDeliveryStatus = $order->delivery_status;
        $oldOrderStatus = $order->order_status;
        $order->delivery_status = 'delivered';
        $order->order_status = 'delivered';
        $order->save();

        // Log activity
        ActivityLogService::log(
            'order.delivery_confirmed',
            "Order #{$order->order_number} delivery confirmed by customer",
            $order,
            [
                'delivery_status' => $oldDeliveryStatus,
                'order_status' => $oldOrderStatus,
            ],
            [
                'delivery_status' => 'delivered',
                'order_status' => 'delivered',
            ],
            null,
            $request
        );

        // Initiate transfer to vendor
        $transferResult = $this->initiateVendorTransfer($order);

        return response()->json([
            'success' => true,
            'message' => 'Delivery confirmed' . ($transferResult['success'] ? ' and payment transfer initiated' : '. Transfer initiation pending'),
            'data' => [
                'order' => $order->load('items', 'shop', 'deliveryAddress'),
                'transfer' => $transferResult['transfer'] ?? null,
            ],
        ]);
    }

    /**
     * Initiate Paystack transfer to vendor after delivery confirmation
     */
    private function initiateVendorTransfer(Order $order)
    {
        try {
            $vendor = $order->shop->vendor;
            if (!$vendor) {
                return [
                    'success' => false,
                    'message' => 'Vendor not found',
                ];
            }

            // Get vendor's first wallet (as per requirement)
            $vendorWallet = \App\Models\VendorWallet::where('vendor_id', $vendor->id)
                ->whereIn('payment_type', ['mobile_money', 'bank_account'])
                ->first();

            if (!$vendorWallet) {
                return [
                    'success' => false,
                    'message' => 'Vendor payment details not found',
                ];
            }

            // Calculate amount to transfer (order total minus processing fee if applicable)
            $feeSettings = \App\Models\FeeSetting::getSettings();
            $transferAmount = $order->total_amount;

            // Deduct processing fee if applicable to vendor
            if ($feeSettings->processing_fee_applicable_to === 'vendor' || 
                $feeSettings->processing_fee_applicable_to === 'both') {
                if ($feeSettings->processing_fee_type === 'percentage') {
                    $processingFee = $transferAmount * ($feeSettings->processing_fee_value / 100);
                } else {
                    $processingFee = $feeSettings->processing_fee_value;
                }
                $transferAmount -= $processingFee;
            } else {
                $processingFee = 0;
            }

            // Deduct platform fee if applicable to vendor
            if ($feeSettings->platform_fee_applicable_to === 'vendor' || 
                $feeSettings->platform_fee_applicable_to === 'both') {
                if ($feeSettings->platform_fee_type === 'percentage') {
                    $platformFee = $order->total_amount * ($feeSettings->platform_fee_value / 100);
                } else {
                    $platformFee = $feeSettings->platform_fee_value;
                }
                $transferAmount -= $platformFee;
            }

            if ($transferAmount <= 0) {
                return [
                    'success' => false,
                    'message' => 'Transfer amount is zero or negative after fees',
                ];
            }

            $paystackService = app(\App\Services\PaystackService::class);

            // Create or get transfer recipient
            $recipientCode = $vendorWallet->recipient_code;
            
            if (!$recipientCode) {
                // Create transfer recipient
                $recipientData = $this->prepareRecipientData($vendorWallet, $vendor);
                $recipientResult = $paystackService->createTransferRecipient($recipientData);

                if (!$recipientResult['success']) {
                    return [
                        'success' => false,
                        'message' => 'Failed to create transfer recipient: ' . ($recipientResult['message'] ?? 'Unknown error'),
                    ];
                }

                $recipientCode = $recipientResult['data']['recipient_code'];
                $vendorWallet->recipient_code = $recipientCode;
                $vendorWallet->save();
            }

            // Generate transfer reference
            $transferReference = $paystackService->generateTransferReference();

            // Convert amount to kobo (smallest currency unit)
            $amountInKobo = (int)($transferAmount * 100);

            // Initiate transfer
            $transferResult = $paystackService->initiateTransfer(
                $recipientCode,
                $amountInKobo,
                "Payment for order {$order->order_number}",
                $transferReference
            );

            // Map Paystack status to our enum values
            $paystackStatus = $transferResult['success'] ? ($transferResult['data']['status'] ?? 'pending') : 'failed';
            // Map 'otp' status to 'pending' (OTP verification is a pending state)
            $mappedStatus = $paystackStatus === 'otp' ? 'pending' : $paystackStatus;
            // Ensure status is one of our enum values
            $validStatuses = ['pending', 'queued', 'success', 'failed', 'reversed'];
            $finalStatus = in_array($mappedStatus, $validStatuses) ? $mappedStatus : 'pending';

            // Create transfer record
            $transfer = \App\Models\Transfer::create([
                'order_id' => $order->id,
                'vendor_id' => $vendor->id,
                'vendor_wallet_id' => $vendorWallet->id,
                'transfer_reference' => $transferReference,
                'recipient_code' => $recipientCode,
                'transfer_code' => $transferResult['success'] ? ($transferResult['data']['transfer_code'] ?? null) : null,
                'amount' => $transferAmount,
                'processing_fee' => $processingFee,
                'currency' => 'GHS', // Default to GHS for Ghana
                'status' => $finalStatus,
                'reason' => "Payment for order {$order->order_number}",
                'failure_reason' => $transferResult['success'] ? null : ($transferResult['message'] ?? 'Transfer initiation failed'),
                'paystack_response' => $transferResult['success'] ? $transferResult['data'] : null,
                'transferred_at' => $transferResult['success'] && ($paystackStatus === 'success') ? now() : null,
            ]);

            return [
                'success' => $transferResult['success'],
                'message' => $transferResult['message'] ?? 'Transfer initiated',
                'transfer' => $transfer,
            ];
        } catch (\Exception $e) {
            \Log::error('Vendor Transfer Exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while initiating transfer: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare recipient data for Paystack based on wallet type
     */
    private function prepareRecipientData(\App\Models\VendorWallet $wallet, \App\Models\Vendor $vendor)
    {
        $recipientData = [
            'name' => $vendor->full_name ?? $wallet->account_name ?? 'Vendor',
        ];

        if ($wallet->payment_type === 'mobile_money') {
            $recipientData['type'] = 'mobile_money';
            $recipientData['currency'] = 'GHS';
            $recipientData['account_number'] = $wallet->momo_number;
            $recipientData['bank_code'] = $wallet->bank_code;
            
            // Paystack mobile money doesn't require provider in the recipient creation
            // The provider is determined by the account number format
        } elseif ($wallet->payment_type === 'bank_account') {
            // Validate required fields for bank account
            if (!$wallet->account_number) {
                throw new \Exception('Bank account number is required');
            }
            
            if (!$wallet->bank_code) {
                throw new \Exception('Bank code is required for bank account transfers. Please update your payment details with the bank code.');
            }

            $recipientData['type'] = 'ghipss'; // Ghana banks
            $recipientData['currency'] = 'GHS';
            $recipientData['account_number'] = $wallet->account_number;
            $recipientData['bank_code'] = $wallet->bank_code; // Required field
        } else {
            throw new \Exception('Unsupported payment type: ' . $wallet->payment_type);
        }

        return $recipientData;
    }
}

