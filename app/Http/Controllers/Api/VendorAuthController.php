<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ArkeselSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VendorAuthController extends Controller
{
    protected $smsService;

    public function __construct(ArkeselSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    // Send OTP
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Send OTP via Arkesel SMS service
        $result = $this->smsService->generateOtp($request->phone);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify OTP via Arkesel SMS service
        try {
            $result = $this->smsService->verifyOtp($request->phone, $request->otp);

            if ($result['success']) {
                // Update or create user phone verification status
                $user = User::where('phone', $request->phone)->first();
                if ($user) {
                    $user->update([
                        'phone_verified_at' => now(),
                        'otp' => null,
                        'otp_expires_at' => null,
                    ]);
                } else {
                    // Create a minimal user record for verified phone (will be updated during registration)
                    // Use temporary values that won't break validation
                    $user = User::create([
                        'name' => 'Temp User', // Temporary name, will be updated during registration
                        'email' => 'temp_' . $request->phone . '@temp.com', // Temporary email
                        'phone' => $request->phone,
                        'phone_verified_at' => now(),
                        'password' => Hash::make(Str::random(32)), // Temporary password, will be updated during registration
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                ]);
            } else {
                // Return the error message from Arkesel API
                $responseData = [
                    'success' => false,
                    'message' => $result['message'] ?? 'OTP verification failed',
                ];
                
                // Include OTP expired flag if applicable
                if (isset($result['otp_expired']) && $result['otp_expired']) {
                    $responseData['otp_expired'] = true;
                }
                
                return response()->json($responseData, 400);
            }
        } catch (\Exception $e) {
            \Log::error('Vendor OTP Verification Error', [
                'message' => $e->getMessage(),
                'phone' => $request->phone,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying OTP. Please try again.',
            ], 500);
        }
    }

    // Register Vendor
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'business_email' => 'nullable|email|unique:vendors,business_email',
            'phone' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if phone is verified - user must exist and be verified
        $user = User::where('phone', $request->phone)->first();
        if (!$user || !$user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your phone number first',
            ], 400);
        }

        // Check if user already has a vendor account
        if ($user->vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor account already exists for this phone number',
            ], 400);
        }

        // Generate a temporary email if not provided
        $userEmail = $request->business_email ?? 'vendor_' . $request->phone . '@temp.com';
        
        // Update user with registration details
        $user->update([
            'name' => $request->full_name,
            'email' => $userEmail,
            'password' => Hash::make($request->password),
            'user_type' => 'vendor',
        ]);

        // Create vendor
        $vendorData = [
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'status' => 'pending',
        ];
        
        // Only add business_email if provided
        if ($request->has('business_email') && $request->business_email) {
            $vendorData['business_email'] = $request->business_email;
        }
        
        $vendor = Vendor::create($vendorData);

        // Generate token
        $token = $user->createToken('vendor-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Vendor registered successfully',
            'data' => [
                'user' => $user,
                'vendor' => $vendor,
                'token' => $token,
            ],
        ], 201);
    }

    // Login Vendor
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)
            ->where('user_type', 'vendor')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('vendor-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'vendor' => $user->vendor,
                'token' => $token,
            ],
        ]);
    }

    // Send OTP for Password Reset
    public function sendPasswordResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user exists and is a vendor
        $user = User::where('phone', $request->phone)
            ->where('user_type', 'vendor')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No vendor account found with this phone number',
            ], 404);
        }

        // Send OTP via Arkesel SMS service
        $result = $this->smsService->generateOtp($request->phone);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }
    }

    // Verify OTP for Password Reset
    public function verifyPasswordResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user exists and is a vendor
        $user = User::where('phone', $request->phone)
            ->where('user_type', 'vendor')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No vendor account found with this phone number',
            ], 404);
        }

        // Verify OTP via Arkesel SMS service
        try {
            $result = $this->smsService->verifyOtp($request->phone, $request->otp);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully. You can now reset your password.',
                ]);
            } else {
                $responseData = [
                    'success' => false,
                    'message' => $result['message'] ?? 'OTP verification failed',
                ];
                
                if (isset($result['otp_expired']) && $result['otp_expired']) {
                    $responseData['otp_expired'] = true;
                }
                
                return response()->json($responseData, 400);
            }
        } catch (\Exception $e) {
            \Log::error('Vendor Password Reset OTP Verification Error', [
                'message' => $e->getMessage(),
                'phone' => $request->phone,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying OTP. Please try again.',
            ], 500);
        }
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user exists and is a vendor
        $user = User::where('phone', $request->phone)
            ->where('user_type', 'vendor')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No vendor account found with this phone number',
            ], 404);
        }

        // Verify OTP one more time before resetting password
        try {
            $result = $this->smsService->verifyOtp($request->phone, $request->otp);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'OTP verification failed. Please request a new OTP.',
                ], 400);
            }

            // OTP verified, update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Vendor Password Reset Error', [
                'message' => $e->getMessage(),
                'phone' => $request->phone,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resetting password. Please try again.',
            ], 500);
        }
    }
}

