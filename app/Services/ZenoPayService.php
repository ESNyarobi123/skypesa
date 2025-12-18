<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZenoPayService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('zenopay.api_key');
        $this->baseUrl = config('zenopay.base_url');
        $this->timeout = config('zenopay.timeout', 30);
    }

    /**
     * Initiate a mobile money payment
     */
    public function initiatePayment(
        string $buyerName,
        string $buyerEmail,
        string $buyerPhone,
        float $amount,
        ?string $orderId = null
    ): array {
        $orderId = $orderId ?? $this->generateOrderId();
        
        // Format phone number (ensure 07XXXXXXXX format)
        $phone = $this->formatPhoneNumber($buyerPhone);
        
        // Demo mode when API key is not set
        if (empty($this->apiKey)) {
            Log::info('ZenoPay Demo Mode - Payment initiated', [
                'order_id' => $orderId,
                'amount' => $amount,
                'phone' => $phone,
            ]);
            
            // Store demo payment in cache for status checking
            \Cache::put("demo_payment_{$orderId}", [
                'status' => 'PENDING',
                'created_at' => now()->timestamp,
                'amount' => $amount,
                'phone' => $phone,
            ], 600); // 10 minutes
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'message' => '[DEMO] Malipo yako yanasimuliwa. Subiri sekunde 5...',
                'demo_mode' => true,
            ];
        }
        
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                ])
                ->post($this->baseUrl . config('zenopay.endpoints.payment'), [
                    'order_id' => $orderId,
                    'buyer_email' => $buyerEmail,
                    'buyer_name' => $buyerName,
                    'buyer_phone' => $phone,
                    'amount' => (int) $amount,
                ]);

            $data = $response->json();
            
            Log::info('ZenoPay Payment Initiated', [
                'order_id' => $orderId,
                'amount' => $amount,
                'phone' => $phone,
                'response' => $data,
            ]);

            if ($response->successful() && ($data['status'] ?? '') === 'success') {
                return [
                    'success' => true,
                    'order_id' => $orderId,
                    'message' => $data['message'] ?? 'Ombi limetumwa. Utapokea PUSH kwenye simu yako.',
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'order_id' => $orderId,
                'message' => $data['message'] ?? 'Kuna tatizo la malipo. Jaribu tena.',
                'error' => $data,
            ];

        } catch (\Exception $e) {
            Log::error('ZenoPay Payment Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'order_id' => $orderId,
                'message' => 'Kuna tatizo la mtandao. Jaribu tena.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check payment status by order ID
     */
    public function checkStatus(string $orderId): array
    {
        // Demo mode - check cached demo payment
        $demoPayment = \Cache::get("demo_payment_{$orderId}");
        if ($demoPayment) {
            $secondsElapsed = now()->timestamp - $demoPayment['created_at'];
            
            // After 5 seconds, mark as completed (simulating successful payment)
            if ($secondsElapsed >= 5) {
                \Cache::forget("demo_payment_{$orderId}");
                
                Log::info('ZenoPay Demo Mode - Payment completed', [
                    'order_id' => $orderId,
                    'seconds_elapsed' => $secondsElapsed,
                ]);
                
                return [
                    'success' => true,
                    'order_id' => $orderId,
                    'status' => 'COMPLETED',
                    'is_completed' => true,
                    'is_pending' => false,
                    'is_failed' => false,
                    'transaction_id' => 'DEMO_' . strtoupper(\Str::random(10)),
                    'channel' => 'M-Pesa',
                    'reference' => 'DEMO_REF_' . time(),
                    'amount' => $demoPayment['amount'],
                    'demo_mode' => true,
                ];
            }
            
            // Still pending
            return [
                'success' => true,
                'order_id' => $orderId,
                'status' => 'PENDING',
                'is_completed' => false,
                'is_pending' => true,
                'is_failed' => false,
                'demo_mode' => true,
                'seconds_remaining' => 5 - $secondsElapsed,
            ];
        }
        
        // If no demo payment and no API key, return not found
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'order_id' => $orderId,
                'status' => 'NOT_FOUND',
                'is_completed' => false,
                'is_pending' => false,
                'is_failed' => true,
                'message' => 'Order haipo.',
                'demo_mode' => true,
            ];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                ])
                ->get($this->baseUrl . config('zenopay.endpoints.status'), [
                    'order_id' => $orderId,
                ]);

            $data = $response->json();
            
            Log::info('ZenoPay Status Check', [
                'order_id' => $orderId,
                'response' => $data,
            ]);

            if ($response->successful() && ($data['result'] ?? '') === 'SUCCESS') {
                $paymentData = $data['data'][0] ?? [];
                $paymentStatus = strtoupper($paymentData['payment_status'] ?? 'PENDING');
                
                return [
                    'success' => true,
                    'order_id' => $orderId,
                    'status' => $paymentStatus,
                    'is_completed' => $paymentStatus === 'COMPLETED',
                    'is_pending' => $paymentStatus === 'PENDING',
                    'is_failed' => in_array($paymentStatus, ['FAILED', 'CANCELLED', 'EXPIRED']),
                    'transaction_id' => $paymentData['transid'] ?? null,
                    'channel' => $paymentData['channel'] ?? null,
                    'reference' => $paymentData['reference'] ?? null,
                    'amount' => $paymentData['amount'] ?? null,
                    'data' => $paymentData,
                ];
            }

            return [
                'success' => false,
                'order_id' => $orderId,
                'status' => 'UNKNOWN',
                'is_completed' => false,
                'is_pending' => false,
                'is_failed' => false,
                'message' => $data['message'] ?? 'Haiwezi kuangalia hali ya malipo.',
            ];

        } catch (\Exception $e) {
            Log::error('ZenoPay Status Check Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'order_id' => $orderId,
                'status' => 'ERROR',
                'is_completed' => false,
                'is_pending' => false,
                'is_failed' => false,
                'message' => 'Kuna tatizo la mtandao.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Poll for payment completion (synchronous)
     */
    public function pollForCompletion(string $orderId, int $maxAttempts = null, int $intervalSeconds = null): array
    {
        $maxAttempts = $maxAttempts ?? config('zenopay.polling.max_attempts', 30);
        $interval = $intervalSeconds ?? config('zenopay.polling.interval', 5);
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $status = $this->checkStatus($orderId);
            
            if ($status['is_completed']) {
                return [
                    'success' => true,
                    'status' => 'COMPLETED',
                    'message' => 'Malipo yamekamilika!',
                    'data' => $status,
                    'attempts' => $attempt,
                ];
            }
            
            if ($status['is_failed']) {
                return [
                    'success' => false,
                    'status' => $status['status'],
                    'message' => 'Malipo yameshindwa.',
                    'data' => $status,
                    'attempts' => $attempt,
                ];
            }
            
            // Wait before next poll
            if ($attempt < $maxAttempts) {
                sleep($interval);
            }
        }
        
        return [
            'success' => false,
            'status' => 'TIMEOUT',
            'message' => 'Muda wa kusubiri umekwisha. Angalia tena baadaye.',
            'attempts' => $maxAttempts,
        ];
    }

    /**
     * Generate unique order ID
     */
    public function generateOrderId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Format phone number to 07XXXXXXXX
     */
    public function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle 255 prefix
        if (str_starts_with($phone, '255')) {
            $phone = '0' . substr($phone, 3);
        }
        
        // Handle +255 prefix
        if (str_starts_with($phone, '+255')) {
            $phone = '0' . substr($phone, 4);
        }
        
        // Ensure starts with 0
        if (!str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }
        
        return $phone;
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'API key haijawekwa.',
            ];
        }

        // We'll try a status check with a dummy order ID to test connection
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                ])
                ->get($this->baseUrl . config('zenopay.endpoints.status'), [
                    'order_id' => 'test-connection-' . time(),
                ]);

            // Even if order not found, if we get a response, API is working
            if ($response->status() !== 500) {
                return [
                    'success' => true,
                    'message' => 'Umeunganishwa na ZenoPay!',
                ];
            }

            return [
                'success' => false,
                'message' => 'ZenoPay API Error',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection Error: ' . $e->getMessage(),
            ];
        }
    }
}
