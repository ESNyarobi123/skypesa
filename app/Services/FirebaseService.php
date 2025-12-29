<?php

namespace App\Services;

use App\Models\PushNotification;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FirebaseService
{
    private string $projectId;
    private string $credentialsPath;
    private ?array $credentials = null;
    
    public function __construct()
    {
        $this->credentialsPath = base_path('sky-pesa-firebase-adminsdk-fbsvc-6ac6dd3f6d.json');
        $this->loadCredentials();
    }
    
    /**
     * Load Firebase credentials from JSON file
     */
    private function loadCredentials(): void
    {
        if (!file_exists($this->credentialsPath)) {
            Log::error('Firebase credentials file not found: ' . $this->credentialsPath);
            return;
        }
        
        $content = file_get_contents($this->credentialsPath);
        $this->credentials = json_decode($content, true);
        $this->projectId = $this->credentials['project_id'] ?? 'sky-pesa';
    }
    
    /**
     * Get OAuth2 access token for Firebase
     */
    private function getAccessToken(): ?string
    {
        // Cache the token for 55 minutes (tokens last 60 min)
        return Cache::remember('firebase_access_token', 3300, function () {
            if (!$this->credentials) {
                return null;
            }
            
            try {
                $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
                
                $credentials = new ServiceAccountCredentials($scopes, $this->credentials);
                $token = $credentials->fetchAuthToken();
                
                return $token['access_token'] ?? null;
            } catch (\Exception $e) {
                Log::error('Failed to get Firebase access token: ' . $e->getMessage());
                return null;
            }
        });
    }
    
    /**
     * Send push notification to a single device
     */
    public function sendToDevice(string $fcmToken, string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Failed to obtain Firebase access token',
            ];
        }
        
        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $this->prepareData($data),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'skypesa_notifications',
                        'sound' => 'default',
                        'default_vibrate_timings' => true,
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ],
        ];
        
        if ($imageUrl) {
            $message['message']['notification']['image'] = $imageUrl;
        }
        
        return $this->sendRequest($message);
    }
    
    /**
     * Send push notification to multiple devices
     */
    public function sendToMultipleDevices(array $fcmTokens, string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        $results = [
            'total' => count($fcmTokens),
            'success' => 0,
            'failure' => 0,
            'errors' => [],
        ];
        
        // FCM HTTP v1 API doesn't support sending to multiple tokens in one request
        // We need to send individual requests (or use batch requests)
        foreach ($fcmTokens as $token) {
            $result = $this->sendToDevice($token, $title, $body, $data, $imageUrl);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failure']++;
                $results['errors'][] = [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $result['error'] ?? 'Unknown error',
                ];
            }
            
            // Small delay to avoid rate limiting
            usleep(50000); // 50ms
        }
        
        return $results;
    }
    
    /**
     * Send push notification to all users with FCM tokens
     */
    public function sendToAll(string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        $tokens = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->where('is_active', true)
            ->where('role', 'user')
            ->pluck('fcm_token')
            ->toArray();
        
        return $this->sendToMultipleDevices($tokens, $title, $body, $data, $imageUrl);
    }
    
    /**
     * Send push notification to a specific segment
     */
    public function sendToSegment(string $segment, string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        $query = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->where('is_active', true)
            ->where('role', 'user');
        
        switch ($segment) {
            case 'premium':
                // Users with active paid subscriptions
                $query->whereHas('activeSubscription.plan', function ($q) {
                    $q->where('price', '>', 0);
                });
                break;
                
            case 'free':
                // Users with free plan
                $query->whereHas('activeSubscription.plan', function ($q) {
                    $q->where('price', 0);
                });
                break;
                
            case 'inactive':
                // Users who haven't logged in for 7 days
                $query->where('last_login_at', '<', now()->subDays(7));
                break;
                
            case 'active':
                // Users who logged in within 24 hours
                $query->where('last_login_at', '>=', now()->subDay());
                break;
                
            case 'new':
                // Users registered in last 7 days
                $query->where('created_at', '>=', now()->subDays(7));
                break;
                
            default:
                // All users
                break;
        }
        
        $tokens = $query->pluck('fcm_token')->toArray();
        
        return $this->sendToMultipleDevices($tokens, $title, $body, $data, $imageUrl);
    }
    
    /**
     * Send push notification to specific users
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        $tokens = User::whereIn('id', $userIds)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->toArray();
        
        return $this->sendToMultipleDevices($tokens, $title, $body, $data, $imageUrl);
    }
    
    /**
     * Send notification and save to in-app notifications as well
     */
    public function sendWithInAppNotification(
        string $targetType,
        ?array $targetUsers,
        ?string $segment,
        string $title,
        string $body,
        array $data = [],
        ?string $imageUrl = null,
        int $sentBy = 0
    ): PushNotification {
        // Create push notification record
        $pushNotification = PushNotification::create([
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'image_url' => $imageUrl,
            'target_type' => $targetType,
            'target_users' => $targetUsers,
            'segment' => $segment,
            'status' => 'sending',
            'sent_by' => $sentBy,
            'sent_at' => now(),
        ]);
        
        try {
            // Send push notification based on target type
            $results = match($targetType) {
                'all' => $this->sendToAll($title, $body, $data, $imageUrl),
                'specific' => $this->sendToUsers($targetUsers ?? [], $title, $body, $data, $imageUrl),
                'segment' => $this->sendToSegment($segment ?? 'all', $title, $body, $data, $imageUrl),
                default => ['total' => 0, 'success' => 0, 'failure' => 0, 'errors' => []],
            };
            
            // Update push notification record
            $pushNotification->update([
                'total_tokens' => $results['total'],
                'success_count' => $results['success'],
                'failure_count' => $results['failure'],
                'error_details' => !empty($results['errors']) ? $results['errors'] : null,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            // Also create in-app notifications for users
            $this->createInAppNotifications($targetType, $targetUsers, $segment, $title, $body, $data);
            
        } catch (\Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage());
            
            $pushNotification->update([
                'status' => 'failed',
                'error_details' => [['error' => $e->getMessage()]],
                'completed_at' => now(),
            ]);
        }
        
        return $pushNotification->refresh();
    }
    
    /**
     * Create in-app notifications for targeted users
     */
    private function createInAppNotifications(
        string $targetType,
        ?array $targetUsers,
        ?string $segment,
        string $title,
        string $body,
        array $data = []
    ): void {
        $query = User::where('role', 'user')->where('is_active', true);
        
        switch ($targetType) {
            case 'specific':
                if ($targetUsers && count($targetUsers) > 0) {
                    $query->whereIn('id', $targetUsers);
                } else {
                    return;
                }
                break;
                
            case 'segment':
                $query = $this->applySegmentFilter($query, $segment);
                break;
                
            case 'all':
            default:
                // All users - no additional filter
                break;
        }
        
        $userIds = $query->pluck('id')->toArray();
        
        if (!empty($userIds)) {
            Notification::notifyMany($userIds, 'system', $title, $body, $data);
        }
    }
    
    /**
     * Apply segment filter to query
     */
    private function applySegmentFilter($query, ?string $segment)
    {
        switch ($segment) {
            case 'premium':
                return $query->whereHas('activeSubscription.plan', function ($q) {
                    $q->where('price', '>', 0);
                });
                
            case 'free':
                return $query->whereHas('activeSubscription.plan', function ($q) {
                    $q->where('price', 0);
                });
                
            case 'inactive':
                return $query->where('last_login_at', '<', now()->subDays(7));
                
            case 'active':
                return $query->where('last_login_at', '>=', now()->subDay());
                
            case 'new':
                return $query->where('created_at', '>=', now()->subDays(7));
                
            default:
                return $query;
        }
    }
    
    /**
     * Prepare data for FCM (all values must be strings)
     */
    private function prepareData(array $data): array
    {
        $prepared = [];
        foreach ($data as $key => $value) {
            $prepared[$key] = is_array($value) ? json_encode($value) : (string) $value;
        }
        
        // Add default data
        $prepared['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
        $prepared['timestamp'] = (string) now()->timestamp;
        
        return $prepared;
    }
    
    /**
     * Send HTTP request to Firebase
     */
    private function sendRequest(array $message): array
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Failed to obtain Firebase access token',
            ];
        }
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $message);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('name'),
                ];
            }
            
            $error = $response->json();
            
            // Check if token is invalid and should be removed
            if (isset($error['error']['details'])) {
                foreach ($error['error']['details'] as $detail) {
                    if (isset($detail['errorCode']) && $detail['errorCode'] === 'UNREGISTERED') {
                        // Token is invalid - remove from database
                        $this->removeInvalidToken($message['message']['token'] ?? null);
                    }
                }
            }
            
            return [
                'success' => false,
                'error' => $error['error']['message'] ?? 'Unknown error',
                'code' => $error['error']['code'] ?? null,
            ];
            
        } catch (\Exception $e) {
            Log::error('Firebase request failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Remove invalid FCM token from database
     */
    private function removeInvalidToken(?string $token): void
    {
        if (!$token) return;
        
        User::where('fcm_token', $token)->update([
            'fcm_token' => null,
            'fcm_token_updated_at' => now(),
        ]);
    }
    
    /**
     * Get FCM token statistics
     */
    public function getTokenStats(): array
    {
        return [
            'total_tokens' => User::whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->count(),
            'android_tokens' => User::whereNotNull('fcm_token')
                ->where('device_type', 'android')
                ->count(),
            'ios_tokens' => User::whereNotNull('fcm_token')
                ->where('device_type', 'ios')
                ->count(),
            'web_tokens' => User::whereNotNull('fcm_token')
                ->where('device_type', 'web')
                ->count(),
            'active_users_with_tokens' => User::whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->where('is_active', true)
                ->where('role', 'user')
                ->count(),
        ];
    }
    
    /**
     * Check if Firebase is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->credentials !== null && isset($this->credentials['project_id']);
    }
}
