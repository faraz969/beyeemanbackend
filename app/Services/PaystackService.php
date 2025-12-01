<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    private $secretKey;
    private $publicKey;
    private $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = 'sk_test_9a42723ad0006db7c4ee4b0d2e02056c67650a86';
        $this->publicKey = 'pk_test_37d555dc7ef542815fa91c525503744dacba2101';
    }

    /**
     * Get public key (for mobile app)
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Initialize a transaction
     *
     * @param string $email
     * @param int $amount Amount in kobo (smallest currency unit)
     * @param array $metadata Optional metadata
     * @return array
     */
    public function initializeTransaction($email, $amount, $metadata = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Cache-Control' => 'no-cache',
            ])->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $email,
                'amount' => $amount, // Amount in kobo
                'metadata' => $metadata,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === true) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                    ];
                }
            }

            Log::error('Paystack Initialize Transaction Failed', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to initialize transaction',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Initialize Transaction Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while initializing transaction',
            ];
        }
    }

    /**
     * Verify a transaction
     *
     * @param string $reference
     * @return array
     */
    public function verifyTransaction($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Cache-Control' => 'no-cache',
            ])->get("{$this->baseUrl}/transaction/verify/{$reference}");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === true) {
                    $transaction = $data['data'];
                    return [
                        'success' => true,
                        'status' => $transaction['status'] === 'success',
                        'data' => $transaction,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to verify transaction',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Verify Transaction Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while verifying transaction',
            ];
        }
    }

    /**
     * Create a transfer recipient
     *
     * @param array $recipientData Recipient data (type, name, account_number, bank_code, etc.)
     * @return array
     */
    public function createTransferRecipient(array $recipientData)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Cache-Control' => 'no-cache',
            ])->post("{$this->baseUrl}/transferrecipient", $recipientData);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === true) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                    ];
                }
            }

            Log::error('Paystack Create Transfer Recipient Failed', [
                'receipientData'=>$recipientData,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to create transfer recipient',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Create Transfer Recipient Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while creating transfer recipient',
            ];
        }
    }

    /**
     * Generate a transfer reference (v4 UUID format)
     *
     * @return string
     */
    public function generateTransferReference()
    {
        // Generate v4 UUID and format it for Paystack (lowercase, with dashes)
        $uuid = \Illuminate\Support\Str::uuid()->toString();
        // Prepend with identifier to reduce collision
        return 'trf_' . str_replace('-', '_', $uuid);
    }

    /**
     * Initiate a transfer
     *
     * @param string $recipientCode Paystack recipient code
     * @param int $amount Amount in kobo (smallest currency unit)
     * @param string $reason Transfer reason
     * @param string|null $reference Optional transfer reference (will generate if not provided)
     * @return array
     */
    public function initiateTransfer($recipientCode, $amount, $reason, $reference = null)
    {
        try {
            if (!$reference) {
                $reference = $this->generateTransferReference();
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Cache-Control' => 'no-cache',
            ])->post("{$this->baseUrl}/transfer", [
                'source' => 'balance',
                'reason' => $reason,
                'amount' => $amount, // Amount in kobo
                'recipient' => $recipientCode,
                'reference' => $reference,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === true) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                        'reference' => $reference,
                    ];
                }
            }

            Log::error('Paystack Initiate Transfer Failed', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to initiate transfer',
                'reference' => $reference,
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Initiate Transfer Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while initiating transfer',
                'reference' => $reference ?? null,
            ];
        }
    }

    /**
     * Verify a transfer
     *
     * @param string $reference Transfer reference
     * @return array
     */
    public function verifyTransfer($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Cache-Control' => 'no-cache',
            ])->get("{$this->baseUrl}/transfer/verify/{$reference}");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === true) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to verify transfer',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Verify Transfer Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while verifying transfer',
            ];
        }
    }
}

