<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerAddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Get customer addresses
    public function index(Request $request)
    {
        $addresses = CustomerAddress::where('customer_id', $request->user()->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    // Create address
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|in:Home,Work,Other',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'country' => 'required|string',
            'street' => 'required|string',
            'city' => 'nullable|string',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If setting as default, unset other defaults
        if ($request->is_default) {
            CustomerAddress::where('customer_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $address = CustomerAddress::create([
            'customer_id' => $request->user()->id,
            'label' => $request->label,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'country' => $request->country,
            'street' => $request->street,
            'city' => $request->city,
            'is_default' => $request->is_default ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address saved successfully',
            'data' => $address,
        ], 201);
    }

    // Update address
    public function update($id, Request $request)
    {
        $address = CustomerAddress::where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'label' => 'sometimes|required|string|in:Home,Work,Other',
            'address' => 'sometimes|required|string',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'country' => 'sometimes|required|string',
            'street' => 'sometimes|required|string',
            'city' => 'nullable|string',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If setting as default, unset other defaults
        if ($request->is_default) {
            CustomerAddress::where('customer_id', $request->user()->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $address->update($request->only([
            'label', 'address', 'latitude', 'longitude',
            'country', 'street', 'city', 'is_default'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => $address,
        ]);
    }

    // Delete address
    public function destroy($id, Request $request)
    {
        $address = CustomerAddress::where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }
}

