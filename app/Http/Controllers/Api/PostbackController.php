<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PostbackHandlerService;
use App\Services\AdProviderManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Postback Controller
 * 
 * Handles postbacks from all ad providers (Adsterra, Monetag, etc.)
 * Uses the PostbackHandlerService for centralized processing.
 */
class PostbackController extends Controller
{
    protected PostbackHandlerService $postbackService;
    protected AdProviderManager $providerManager;

    public function __construct(
        PostbackHandlerService $postbackService,
        AdProviderManager $providerManager
    ) {
        $this->postbackService = $postbackService;
        $this->providerManager = $providerManager;
    }

    /**
     * Handle Adsterra postback
     * 
     * URL: /api/postback/adsterra
     */
    public function adsterra(Request $request)
    {
        Log::info('Adsterra postback received', [
            'ip' => $request->ip(),
            'data' => $request->all(),
        ]);

        $result = $this->postbackService->handlePostback('adsterra', $request);

        return response()->json([
            'status' => $result['success'] ? 'ok' : 'error',
            'message' => $result['message'] ?? null,
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Handle Monetag postback
     * 
     * URL: /api/postback/monetag
     */
    public function monetag(Request $request)
    {
        Log::info('Monetag postback received', [
            'ip' => $request->ip(),
            'data' => $request->all(),
        ]);

        $result = $this->postbackService->handlePostback('monetag', $request);

        return response()->json([
            'status' => $result['success'] ? 'ok' : 'error',
            'message' => $result['message'] ?? null,
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Generic postback handler for any provider
     * 
     * URL: /api/postback/{provider}
     */
    public function handle(Request $request, string $provider)
    {
        // Validate provider exists
        if (!$this->providerManager->hasProvider($provider)) {
            Log::warning("Postback received for unknown provider: {$provider}", [
                'ip' => $request->ip(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Unknown provider',
            ], 400);
        }

        Log::info("{$provider} postback received", [
            'ip' => $request->ip(),
            'data' => $request->all(),
        ]);

        $result = $this->postbackService->handlePostback($provider, $request);

        return response()->json([
            'status' => $result['success'] ? 'ok' : 'error',
            'message' => $result['message'] ?? null,
            'completion_id' => $result['completion_id'] ?? null,
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Test postback endpoint (for debugging)
     * 
     * URL: /api/postback/test
     */
    public function test(Request $request)
    {
        Log::info('Test postback received', [
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'data' => $request->all(),
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Test postback received',
            'received_at' => now()->toISOString(),
            'ip' => $request->ip(),
            'data' => $request->all(),
        ]);
    }
}
