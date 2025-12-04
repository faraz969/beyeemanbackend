<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ArkeselSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerAuthController extends Controller
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
                        'email' => 'temp_' . $request->phone . '@temp.com', // Temporary email (nullable for customers)
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
            \Log::error('Customer OTP Verification Error', [
                'message' => $e->getMessage(),
                'phone' => $request->phone,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying OTP. Please try again.',
            ], 500);
        }
    }

    // Register Customer
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
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

        // Check if user already has an account with different user type
        if ($user->user_type && $user->user_type !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'This phone number is already registered as a ' . $user->user_type,
            ], 400);
        }

        // Update existing user with registration details (replacing temporary values)
        $updateData = [
            'name' => $request->full_name,
            'password' => Hash::make($request->password),
            'user_type' => 'customer',
        ];
        
        // Only update email if provided (it's optional for customers)
        // If email was provided, update it; otherwise keep the temp email or null
        if ($request->email) {
            $updateData['email'] = $request->email;
        } elseif (strpos($user->email, 'temp_') === 0) {
            // If no email provided and current email is temp, set to null
            $updateData['email'] = null;
        }
        
        $user->update($updateData);

        // Generate token
        $token = $user->createToken('customer-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Customer registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    // Login Customer
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
            ->where('user_type', 'customer')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('customer-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
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

        // Check if user exists and is a customer
        $user = User::where('phone', $request->phone)
            ->where('user_type', 'customer')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No customer account found with this phone number',
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

        // Check if user exists and is a customer
        $user = User::where('phone', $request->phone)
            ->where('user_type', 'customer')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No customer account found with this phone number',
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
            \Log::error('Customer Password Reset OTP Verification Error', [
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

        // Check if user exists and is a customer
        $user = User::where('phone', $request->phone)
            ->where('user_type', 'customer')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No customer account found with this phone number',
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
            \Log::error('Customer Password Reset Error', [
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

