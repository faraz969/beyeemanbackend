<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class VendorProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Get vendor profile
    public function show(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'phone_verified_at' => $user->phone_verified_at,
                ],
                'vendor' => [
                    'id' => $vendor->id,
                    'full_name' => $vendor->full_name,
                    'business_email' => $vendor->business_email,
                    'phone' => $vendor->phone,
                    'status' => $vendor->status,
                ],
            ],
        ]);
    }

    // Update vendor profile
    public function update(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'business_email' => 'sometimes|required|email|unique:vendors,business_email,' . $vendor->id,
            'phone' => 'sometimes|required|string|unique:users,phone,' . $user->id,
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        $user->save();

        // Update vendor
        if ($request->has('full_name')) {
            $vendor->full_name = $request->full_name;
        }
        if ($request->has('business_email')) {
            $vendor->business_email = $request->business_email;
        }
        if ($request->has('phone')) {
            $vendor->phone = $request->phone;
        }
        $vendor->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user,
                'vendor' => $vendor,
            ],
        ]);
    }

    // Change password
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }
}

