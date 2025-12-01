<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvailabilityRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorAvailabilityRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Get availability requests for vendor's products
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $shop = $vendor->shop;
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found. Please create shop first.',
            ], 404);
        }

        $status = $request->input('status'); // Filter by status: pending, available, out_of_stock, limited

        $requests = AvailabilityRequest::with(['product.images', 'customer'])
            ->whereHas('product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                $primaryImage = $request->product->images()->where('is_primary', true)->first();
                return [
                    'id' => $request->id,
                    'customer_id' => $request->customer_id,
                    'customer_name' => $request->customer->name ?? 'Customer',
                    'customer_phone' => $request->customer->phone ?? '',
                    'product_id' => $request->product_id,
                    'product_name' => $request->product->name,
                    'product_image' => $primaryImage ? asset('storage/' . $primaryImage->image_path) : null,
                    'product_price' => $request->product->price,
                    'product_quantity_available' => $request->product->quantity_available,
                    'requested_quantity' => $request->requested_quantity,
                    'status' => $request->status,
                    'available_quantity' => $request->available_quantity,
                    'vendor_notes' => $request->vendor_notes,
                    'created_at' => $request->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    // Get single availability request
    public function show($id, Request $request)
    {
        $vendor = $request->user()->vendor;
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $shop = $vendor->shop;
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        $availabilityRequest = AvailabilityRequest::with(['product.images', 'customer'])
            ->whereHas('product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->where('id', $id)
            ->first();

        if (!$availabilityRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found',
            ], 404);
        }

        $primaryImage = $availabilityRequest->product->images()->where('is_primary', true)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $availabilityRequest->id,
                'customer_id' => $availabilityRequest->customer_id,
                'customer_name' => $availabilityRequest->customer->name ?? 'Customer',
                'customer_phone' => $availabilityRequest->customer->phone ?? '',
                'product_id' => $availabilityRequest->product_id,
                'product_name' => $availabilityRequest->product->name,
                'product_description' => $availabilityRequest->product->description,
                'product_image' => $primaryImage ? asset('storage/' . $primaryImage->image_path) : null,
                'product_price' => $availabilityRequest->product->price,
                'product_quantity_available' => $availabilityRequest->product->quantity_available,
                'requested_quantity' => $availabilityRequest->requested_quantity,
                'status' => $availabilityRequest->status,
                'available_quantity' => $availabilityRequest->available_quantity,
                'vendor_notes' => $availabilityRequest->vendor_notes,
                'created_at' => $availabilityRequest->created_at,
            ],
        ]);
    }

    // Update availability request status
    public function update($id, Request $request)
    {
        $vendor = $request->user()->vendor;
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $shop = $vendor->shop;
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        $availabilityRequest = AvailabilityRequest::with('product')
            ->whereHas('product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->where('id', $id)
            ->first();

        if (!$availabilityRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,out_of_stock,limited',
            'available_quantity' => 'required_if:status,limited|integer|min:1',
            'vendor_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update status
        $availabilityRequest->status = $request->status;
        
        if ($request->status === 'limited') {
            $availabilityRequest->available_quantity = $request->available_quantity;
        } else {
            $availabilityRequest->available_quantity = null;
        }
        
        if ($request->has('vendor_notes')) {
            $availabilityRequest->vendor_notes = $request->vendor_notes;
        }
        
        $availabilityRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Availability request updated successfully',
            'data' => $availabilityRequest->load('product', 'customer'),
        ]);
    }
}

