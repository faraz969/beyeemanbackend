<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Create/Update Shop
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0',
            'primary_contact' => 'required|string',
            'alternate_contact' => 'nullable|string',
            'business_email' => 'required|email',
            'business_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'country' => 'required|string',
            'street' => 'nullable|string',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
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

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('shop_logo')) {
            $logoPath = $request->file('shop_logo')->store('shop_logos', 'public');
        }

        $shop = Shop::updateOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'shop_name' => $request->shop_name,
                'shop_logo' => $logoPath,
                'description' => $request->description,
                'category' => $request->category,
                'currency' => $request->currency,
                'exchange_rate' => $request->exchange_rate,
                'primary_contact' => $request->primary_contact,
                'alternate_contact' => $request->alternate_contact,
                'business_email' => $request->business_email,
                'business_address' => $request->business_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'country' => $request->country,
                'street' => $request->street,
                'opening_time' => $request->opening_time,
                'closing_time' => $request->closing_time,
                'status' => 'setup',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Shop created successfully',
            'data' => $shop->load('deliveryZones'),
        ], 201);
    }

    // Get Shop
    public function show(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $shop = Shop::where('vendor_id', $vendor->id)
            ->with('deliveryZones')
            ->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        // Add full URL for shop logo
        $shopData = $shop->toArray();
        if ($shop->shop_logo) {
            $shopData['shop_logo_url'] = asset('storage/' . $shop->shop_logo);
        }

        return response()->json([
            'success' => true,
            'data' => $shopData,
        ]);
    }

    // Add Delivery Zone
    public function addDeliveryZone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_name' => 'required|string',
            'delivery_fee' => 'required|numeric|min:0',
            'estimated_delivery_time' => 'required|integer|min:1',
            'delivery_type' => 'required|in:vendor,platform',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;
        $shop = Shop::where('vendor_id', $vendor->id)->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found. Please create shop first.',
            ], 404);
        }

        $deliveryZone = DeliveryZone::create([
            'shop_id' => $shop->id,
            'location_name' => $request->location_name,
            'delivery_fee' => $request->delivery_fee,
            'estimated_delivery_time' => $request->estimated_delivery_time,
            'delivery_type' => $request->delivery_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery zone added successfully',
            'data' => $deliveryZone,
        ], 201);
    }

    // Update Delivery Zone
    public function updateDeliveryZone(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'location_name' => 'required|string',
            'delivery_fee' => 'required|numeric|min:0',
            'estimated_delivery_time' => 'required|integer|min:1',
            'delivery_type' => 'required|in:vendor,platform',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;
        $shop = Shop::where('vendor_id', $vendor->id)->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found.',
            ], 404);
        }

        $deliveryZone = DeliveryZone::where('id', $id)
            ->where('shop_id', $shop->id)
            ->first();

        if (!$deliveryZone) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery zone not found.',
            ], 404);
        }

        $deliveryZone->update([
            'location_name' => $request->location_name,
            'delivery_fee' => $request->delivery_fee,
            'estimated_delivery_time' => $request->estimated_delivery_time,
            'delivery_type' => $request->delivery_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery zone updated successfully',
            'data' => $deliveryZone,
        ]);
    }

    // Delete Delivery Zone
    public function deleteDeliveryZone(Request $request, $id)
    {
        $vendor = $request->user()->vendor;
        $shop = Shop::where('vendor_id', $vendor->id)->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found.',
            ], 404);
        }

        $deliveryZone = DeliveryZone::where('id', $id)
            ->where('shop_id', $shop->id)
            ->first();

        if (!$deliveryZone) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery zone not found.',
            ], 404);
        }

        $deliveryZone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delivery zone deleted successfully',
        ]);
    }

    // Save Payment Details
    public function savePaymentDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_type' => 'required|in:mobile_money,bank_account,internal_wallet',
            'provider' => 'required_if:payment_type,mobile_money|nullable|in:MTN,Vodafone,AirtelTigo',
            'momo_number' => 'required_if:payment_type,mobile_money|nullable|string',
            'account_name' => 'required|string',
            'bank_name' => 'required_if:payment_type,bank_account|nullable|string',
            'account_number' => 'required_if:payment_type,bank_account|nullable|string',
            'bank_code' => 'required|string', // Required for both mobile_money and bank_account
            'branch' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;

        $walletData = [
            'account_name' => $request->account_name,
        ];

        if ($request->payment_type === 'mobile_money') {
            $walletData['provider'] = $request->provider;
            $walletData['momo_number'] = $request->momo_number;
            $walletData['bank_code'] = $request->bank_code; // Provider code for mobile money
        } elseif ($request->payment_type === 'bank_account') {
            $walletData['bank_name'] = $request->bank_name;
            $walletData['account_number'] = $request->account_number;
            $walletData['bank_code'] = $request->bank_code; // Bank code for bank account
            $walletData['branch'] = $request->branch;
        }

        $wallet = \App\Models\VendorWallet::updateOrCreate(
            [
                'vendor_id' => $vendor->id,
                'payment_type' => $request->payment_type,
            ],
            $walletData
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment details saved successfully',
            'data' => $wallet,
        ]);
    }
}

