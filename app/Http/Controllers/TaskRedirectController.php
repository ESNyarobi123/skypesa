<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Services\AdProviderManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Task Redirect Controller
 * 
 * Handles redirect/click tracking for ad provider links.
 * Since Adsterra Smartlink and Monetag Push/IPN don't have postbacks,
 * we track clicks here and rely on timer-based completion.
 * 
 * Flow:
 * 1. User clicks "Start Task" 
 * 2. Frontend calls API to start task (gets lock_token)
 * 3. User is redirected here: /go/{provider}/{task}?token={lock_token}
 * 4. We log the click and redirect to provider URL with tracking params
 * 5. User views ad for required duration
 * 6. User returns and clicks "Complete" 
 * 7. Timer verification happens, reward given
 */
class TaskRedirectController extends Controller
{
    protected AdProviderManager $providerManager;

    public function __construct(AdProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    /**
     * Redirect to provider ad URL with tracking
     * 
     * Route: /go/{provider}/{task}
     */
    public function redirect(Request $request, string $providerName, Task $task)
    {
        $user = Auth::user();
        
        // For non-authenticated requests, try to get user from token
        if (!$user) {
            $lockToken = $request->query('token');
            if ($lockToken) {
                $completion = TaskCompletion::where('lock_token', $lockToken)
                    ->where('task_id', $task->id)
                    ->where('status', 'in_progress')
                    ->first();
                
                if ($completion) {
                    $user = $completion->user;
                }
            }
        }

        // Log the click
        Log::channel('postbacks')->info('Task redirect click', [
            'provider' => $providerName,
            'task_id' => $task->id,
            'user_id' => $user?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'lock_token' => $request->query('token'),
        ]);

        // Get provider and generate tracking URL
        $provider = $this->providerManager->provider($providerName);
        
        if (!$provider) {
            Log::warning("Unknown provider in redirect: {$providerName}");
            return redirect($task->url); // Fallback to raw URL
        }

        // Generate URL with tracking (includes psid/subid for provider reporting)
        if ($user) {
            $trackingUrl = $provider->generateTaskLink($user, $task);
        } else {
            // Anonymous - just use base URL
            $trackingUrl = $task->url;
        }

        // Update completion with click timestamp
        if ($user) {
            TaskCompletion::where('user_id', $user->id)
                ->where('task_id', $task->id)
                ->where('status', 'in_progress')
                ->update([
                    'metadata->clicked_at' => now()->toISOString(),
                    'metadata->click_ip' => $request->ip(),
                ]);
        }

        // Redirect to provider
        return redirect()->away($trackingUrl);
    }

    /**
     * Direct redirect for Adsterra
     * 
     * Route: /go/adsterra/{task}
     * 
     * Adsterra Smartlink uses psid for SubID tracking.
     * Format: https://smartlink...&psid=U{user_id}_T{task_id}
     */
    public function adsterra(Request $request, Task $task)
    {
        return $this->redirect($request, 'adsterra', $task);
    }

    /**
     * Direct redirect for Monetag
     * 
     * Route: /go/monetag/{task}
     * 
     * Monetag uses ymid for custom identifier.
     * Note: Web Push/IPN don't have postbacks - timer-based only.
     */
    public function monetag(Request $request, Task $task)
    {
        return $this->redirect($request, 'monetag', $task);
    }

    /**
     * Generate signed redirect URL
     * 
     * Creates a URL with signature to prevent tampering.
     * Used when generating task URLs in the frontend.
     */
    public static function generateSignedUrl(string $provider, Task $task, User $user, string $lockToken): string
    {
        $signature = self::generateSignature($provider, $task->id, $user->id, $lockToken);
        
        return url("/go/{$provider}/{$task->id}") . '?' . http_build_query([
            'token' => $lockToken,
            'sig' => $signature,
        ]);
    }

    /**
     * Generate HMAC signature for URL protection
     */
    public static function generateSignature(string $provider, int $taskId, int $userId, string $lockToken): string
    {
        $data = "{$provider}:{$taskId}:{$userId}:{$lockToken}";
        return substr(hash_hmac('sha256', $data, config('app.key')), 0, 16);
    }

    /**
     * Verify URL signature
     */
    public static function verifySignature(string $signature, string $provider, int $taskId, int $userId, string $lockToken): bool
    {
        $expected = self::generateSignature($provider, $taskId, $userId, $lockToken);
        return hash_equals($expected, $signature);
    }
}
