<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Services\TaskLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Go Controller - Handles Direct Link Redirects
 * 
 * This controller manages the "click tracking" for incentivized tasks.
 * Since Monetag Direct Links and Adsterra Smartlink don't have postbacks,
 * we track clicks ourselves and use timer-based completion.
 * 
 * Flow:
 * 1. User clicks task → /go/{provider}/{slug}
 * 2. We log the click, verify limits, create TaskCompletion
 * 3. Redirect to provider URL (with psid/tracking if applicable)
 * 4. User views ad, returns
 * 5. User clicks "Complete" → timer verification → reward
 * 
 * ANTI-FRAUD MEASURES:
 * - Daily limit per user
 * - IP-based limits
 * - Cooldown between tasks
 * - Device fingerprint tracking
 * - Small rewards to minimize loss
 */
class GoController extends Controller
{
    protected TaskLockService $lockService;

    public function __construct(TaskLockService $lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * Redirect to Monetag Direct Link
     * 
     * Route: GET /go/monetag/{slug}
     * Slugs: immortal, glad (from config)
     */
    public function monetag(Request $request, string $slug)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Ingia kwanza ili ufanye kazi.');
        }

        // Get Direct Link URL from config
        $directUrl = config("directlinks.monetag.{$slug}");
        
        if (empty($directUrl)) {
            Log::warning('Monetag Direct Link not configured', ['slug' => $slug]);
            return back()->with('error', 'Kazi hii haipatikani kwa sasa.');
        }

        // Find or create task for this slug
        $task = $this->getOrCreateDirectLinkTask('monetag', $slug);

        // Anti-fraud checks
        $check = $this->performAntifraudChecks($user, $task, $request->ip());
        if (!$check['allowed']) {
            return back()->with('error', $check['message']);
        }

        // Start the task (create TaskCompletion with lock)
        $completion = $this->startTask($user, $task, $request);

        // Log the click
        $this->logClick($user, $task, $request, $slug);

        // Add tracking parameter if needed (optional for analytics)
        $trackingUrl = $this->appendTracking($directUrl, $user, $task, 'monetag');

        // Redirect to provider
        return redirect()->away($trackingUrl);
    }

    /**
     * Redirect to Adsterra Smartlink
     * 
     * Route: GET /go/adsterra/{task?}
     */
    public function adsterra(Request $request, ?Task $task = null)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Ingia kwanza ili ufanye kazi.');
        }

        // If no task provided, use default Adsterra task
        if (!$task) {
            $task = $this->getOrCreateDirectLinkTask('adsterra', 'smartlink');
        }

        // Get Smartlink URL
        $smartlinkUrl = $task->url ?: config('directlinks.adsterra.smartlink');
        
        if (empty($smartlinkUrl)) {
            Log::warning('Adsterra Smartlink not configured');
            return back()->with('error', 'Kazi hii haipatikani kwa sasa.');
        }

        // Anti-fraud checks
        $check = $this->performAntifraudChecks($user, $task, $request->ip());
        if (!$check['allowed']) {
            return back()->with('error', $check['message']);
        }

        // Start the task
        $completion = $this->startTask($user, $task, $request);

        // Log the click
        $this->logClick($user, $task, $request, 'adsterra-smartlink');

        // Add psid for Adsterra reporting
        $trackingUrl = $this->appendTracking($smartlinkUrl, $user, $task, 'adsterra');

        return redirect()->away($trackingUrl);
    }

    /**
     * Generic redirect handler
     * 
     * Route: GET /go/{provider}/{slug}
     */
    public function redirect(Request $request, string $provider, string $slug)
    {
        return match($provider) {
            'monetag' => $this->monetag($request, $slug),
            'adsterra' => $this->adsterra($request, Task::find($slug)),
            default => back()->with('error', 'Provider haijulikani.'),
        };
    }

    // ==========================================
    // Anti-Fraud Methods
    // ==========================================

    /**
     * Perform all anti-fraud checks before allowing task
     */
    protected function performAntifraudChecks(User $user, Task $task, string $ip): array
    {
        $config = config('directlinks');

        // 1. Check daily user limit
        $userTodayCount = TaskCompletion::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        if ($userTodayCount >= $config['daily_limit']) {
            return [
                'allowed' => false,
                'message' => "Umekamilisha kazi {$config['daily_limit']} leo. Rudi kesho!",
            ];
        }

        // 2. Check IP daily limit
        $ipTodayCount = TaskCompletion::where('ip_address', $ip)
            ->whereDate('created_at', today())
            ->count();

        if ($ipTodayCount >= $config['ip_daily_limit']) {
            return [
                'allowed' => false,
                'message' => 'Kikomo cha kazi kimefikiwa. Jaribu tena kesho.',
            ];
        }

        // 3. Check cooldown
        $lastCompletion = TaskCompletion::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastCompletion) {
            $cooldownEnds = $lastCompletion->created_at->addSeconds($config['cooldown_seconds']);
            if (now()->lt($cooldownEnds)) {
                $remaining = now()->diffInSeconds($cooldownEnds);
                return [
                    'allowed' => false,
                    'message' => "Subiri sekunde {$remaining} kabla ya kazi nyingine.",
                ];
            }
        }

        // 4. Check if user already has task in progress
        $inProgress = TaskCompletion::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->where('created_at', '>', now()->subMinutes($config['max_completion_window']))
            ->first();

        if ($inProgress) {
            return [
                'allowed' => false,
                'message' => 'Una kazi inayoendelea. Kamilisha kwanza!',
            ];
        }

        // 5. Check task-specific limits
        if (!$task->canUserComplete($user)) {
            return [
                'allowed' => false,
                'message' => 'Umekamilisha kazi hii mara nyingi leo.',
            ];
        }

        if (!$task->canIPComplete($ip)) {
            return [
                'allowed' => false,
                'message' => 'Kikomo cha kazi hii kimefikiwa.',
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    // ==========================================
    // Task Management Methods
    // ==========================================

    /**
     * Start a task - create TaskCompletion with lock
     */
    protected function startTask(User $user, Task $task, Request $request): TaskCompletion
    {
        // Create TaskCompletion
        $completion = TaskCompletion::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'status' => 'in_progress',
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_fingerprint' => $request->header('X-Device-Fingerprint', 
                                        $request->cookie('device_fingerprint')),
            'required_duration' => $task->duration_seconds ?? config('directlinks.min_duration'),
            'lock_token' => $this->generateLockToken(),
            'is_locked' => true,
            'provider' => $task->provider,
            'metadata' => [
                'click_time' => now()->toISOString(),
                'referrer' => $request->header('Referer'),
            ],
        ]);

        // Store in cache for quick lookup
        Cache::put(
            "task_lock:{$user->id}:{$task->id}",
            $completion->lock_token,
            now()->addMinutes(config('directlinks.max_completion_window'))
        );

        return $completion;
    }

    /**
     * Get or create Task record for a Direct Link
     */
    protected function getOrCreateDirectLinkTask(string $provider, string $slug): Task
    {
        $taskKey = "{$provider}_{$slug}";
        
        $task = Task::where('provider', $provider)
            ->where('type', 'view_ad')
            ->where('requirements->slug', $slug)
            ->first();

        if (!$task) {
            $config = config('directlinks');
            
            $task = Task::create([
                'title' => $config['titles'][$taskKey] ?? "Tazama Tangazo - {$slug}",
                'description' => $config['descriptions'][$taskKey] ?? 'Bofya na utazame kwa sekunde 30.',
                'type' => 'view_ad',
                'category' => 'traffic_task',
                'require_postback' => false, // NO POSTBACK for Direct Links!
                'url' => $provider === 'monetag' 
                    ? config("directlinks.monetag.{$slug}") 
                    : config('directlinks.adsterra.smartlink'),
                'provider' => $provider,
                'duration_seconds' => $config['min_duration'],
                'cooldown_seconds' => $config['cooldown_seconds'],
                'daily_limit' => 3, // Per task per user
                'ip_daily_limit' => 5, // Per task per IP
                'reward_override' => $config['default_reward'],
                'is_active' => true,
                'requirements' => [
                    'source' => 'directlink',
                    'slug' => $slug,
                ],
            ]);

            Log::info("Created Direct Link task: {$taskKey}", ['task_id' => $task->id]);
        }

        return $task;
    }

    // ==========================================
    // Tracking & Logging Methods
    // ==========================================

    /**
     * Append tracking parameters to URL
     */
    protected function appendTracking(string $url, User $user, Task $task, string $provider): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';
        
        if ($provider === 'adsterra') {
            // Adsterra uses psid for SubID tracking
            $psid = "U{$user->id}_T{$task->id}";
            return $url . $separator . http_build_query(['psid' => $psid]);
        }
        
        if ($provider === 'monetag') {
            // Monetag Direct Links - optional tracking
            // Note: This is just for our own analytics, Monetag doesn't use it
            return $url; // Return as-is, or add custom tracking if needed
        }
        
        return $url;
    }

    /**
     * Log click for analytics and fraud detection
     */
    protected function logClick(User $user, Task $task, Request $request, string $slug): void
    {
        Log::channel('postbacks')->info('Direct Link click', [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'slug' => $slug,
            'provider' => $task->provider,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_fingerprint' => $request->header('X-Device-Fingerprint'),
            'referrer' => $request->header('Referer'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Generate unique lock token
     */
    protected function generateLockToken(): string
    {
        return bin2hex(random_bytes(16));
    }
}
