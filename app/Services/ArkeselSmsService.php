<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArkeselSmsService
{
    private $apiKey;
    private $baseUrl;
    private $senderId;

    public function __construct()
    {
        $this->apiKey = 'Ok1GNWlYWFB0VHI1NHJZUUQ=';
        $this->baseUrl = 'https://sms.arkesel.com/api';
        $this->senderId = 'Beyeeman';
    }

    /**
     * Format phone number by removing + sign and any spaces
     *
     * @param string $phone
     * @return string
     */
    private function formatPhoneNumber($phone)
    {
        // Remove + sign and spaces
        return str_replace(['+', ' ', '-'], '', $phone);
    }

    /**
     * Generate and send OTP
     *
     * @param string $phoneNumber
     * @return array ['success' => bool, 'message' => string]
     */
    public function generateOtp($phoneNumber)
    {
        $phone = $this->formatPhoneNumber($phoneNumber);

        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
            ])->post("{$this->baseUrl}/otp/generate", [
                'expiry' => 5,
                'length' => 6,
                'medium' => 'sms',
                'message' => 'This is OTP from Beyeemen, %otp_code%',
                'number' => $phone,
                'sender_id' => $this->senderId,
                'type' => 'numeric',
            ]);

            // Check if API call was successful (200 status)
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Check if OTP was sent successfully (code 1000)
                // Handle both string and integer codes
                $code = (string) ($responseData['code'] ?? '');
                if ($code === '1000') {
                    return [
                        'success' => true,
                        'message' => $responseData['message'] ?? 'OTP sent successfully',
                    ];
                } else {
                    // OTP generation failed - return the API error message
                    return [
                        'success' => false,
                        'message' => $responseData['message'] ?? 'Failed to send OTP',
                        'code' => $responseData['code'] ?? null,
                    ];
                }
            } else {
                // HTTP request failed - try to get error message from response
                $responseData = $response->json();
                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Failed to connect to SMS service',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Arkesel OTP Generation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while sending OTP',
            ];
        }
    }

    /**
     * Verify OTP
     *
     * @param string $phoneNumber
     * @param string $code
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyOtp($phoneNumber, $code)
    {
        $phone = $this->formatPhoneNumber($phoneNumber);

        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
            ])->post("{$this->baseUrl}/otp/verify", [
                'code' => $code,
                'number' => $phone,
            ]);

            // Get response data - handle both JSON and non-JSON responses
            $responseData = [];
            try {
                $responseData = $response->json();
            } catch (\Exception $e) {
                Log::warning('Arkesel OTP Verify: Response is not JSON. Status: ' . $response->status() . ', Body: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'Invalid response from SMS service',
                ];
            }

            // Log the response for debugging
            Log::info('Arkesel OTP Verify Response', [
                'status' => $response->status(),
                'response' => $responseData,
                'phone' => $phone,
            ]);

            // Check if API call was successful (200 status)
            if ($response->successful()) {
                // Check if OTP verification was successful (code 1100)
                // Handle both string and integer codes
                $responseCode = isset($responseData['code']) ? (string) $responseData['code'] : '';
                
                if ($responseCode === '1100') {
                    return [
                        'success' => true,
                        'message' => $responseData['message'] ?? 'OTP verified successfully',
                    ];
                } else {
                    // OTP verification failed - return the API error message
                    $errorMessage = $responseData['message'] ?? 'Invalid OTP code';
                    $isExpired = ($responseCode === '1105');
                    
                    return [
                        'success' => false,
                        'message' => $errorMessage,
                        'code' => $responseData['code'] ?? null,
                        'otp_expired' => $isExpired,
                    ];
                }
            } else {
                // HTTP request failed - try to get error message from response
                $errorMessage = $responseData['message'] ?? 'Failed to connect to SMS service';
                return [
                    'success' => false,
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Arkesel OTP Verification Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'An error occurred while verifying OTP. Please try again.',
            ];
        }
    }

    /**
     * Send general SMS message
     *
     * @param string $phoneNumber
     * @param string $message
     * @return array ['success' => bool, 'message' => string, 'balance' => float|null]
     */
    public function sendSms($phoneNumber, $message)
    {
        $phone = $this->formatPhoneNumber($phoneNumber);

        try {
            $response = Http::get("https://sms.arkesel.com/sms/api", [
                'action' => 'send-sms',
                'api_key' => $this->apiKey,
                'to' => $phone,
                'from' => $this->senderId,
                'sms' => $message,
            ]);

            // Log the response for debugging
            Log::info('Arkesel Send SMS Response', [
                'status' => $response->status(),
                'response' => $response->json(),
                'phone' => $phone,
            ]);

            // Check if API call was successful (200 status)
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Check if SMS was sent successfully (code "ok")
                if (isset($responseData['code']) && strtolower($responseData['code']) === 'ok') {
                    return [
                        'success' => true,
                        'message' => $responseData['message'] ?? 'SMS sent successfully',
                        'balance' => $responseData['main_balance'] ?? null,
                    ];
                } else {
                    // SMS sending failed
                    return [
                        'success' => false,
                        'message' => $responseData['message'] ?? 'Failed to send SMS',
                        'code' => $responseData['code'] ?? null,
                    ];
                }
            } else {
                // HTTP request failed
                $responseData = $response->json();
                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Failed to connect to SMS service',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Arkesel Send SMS Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'An error occurred while sending SMS',
            ];
        }
    }
}

