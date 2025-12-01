<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvailabilityRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AvailabilityRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Create availability request
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'requested_quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);

        $availabilityRequest = AvailabilityRequest::create([
            'customer_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'requested_quantity' => $request->requested_quantity,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Availability request sent to vendor',
            'data' => $availabilityRequest,
        ], 201);
    }

    // Get customer's availability requests
    public function index(Request $request)
    {
        $requests = AvailabilityRequest::with(['product.shop', 'product.images'])
            ->where('customer_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    // Get single availability request
    public function show($id, Request $request)
    {
        $availabilityRequest = AvailabilityRequest::with(['product.shop', 'product.images'])
            ->where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (!$availabilityRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $availabilityRequest,
        ]);
    }
}

