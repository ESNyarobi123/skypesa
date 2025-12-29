<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminPushNotificationController extends Controller
{
    protected FirebaseService $firebaseService;
    
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    
    /**
     * Display the push notifications dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $status = $request->get('status');
        $targetType = $request->get('target_type');
        
        $query = PushNotification::with('sender')->latest();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($targetType) {
            $query->where('target_type', $targetType);
        }
        
        $notifications = $query->paginate(15);
        
        // Get stats
        $stats = [
            'total_sent' => PushNotification::count(),
            'total_success' => PushNotification::sum('success_count'),
            'total_failure' => PushNotification::sum('failure_count'),
            'pending' => PushNotification::where('status', 'pending')->count(),
        ];
        
        // Get token stats
        $tokenStats = $this->firebaseService->getTokenStats();
        
        // Check Firebase configuration
        $isConfigured = $this->firebaseService->isConfigured();
        
        return view('admin.push-notifications.index', compact(
            'notifications',
            'stats',
            'tokenStats',
            'isConfigured'
        ));
    }
    
    /**
     * Show the form for creating a new push notification
     */
    public function create()
    {
        // Get segment options with user counts
        $segments = $this->getSegmentOptions();
        
        // Get active users for specific targeting
        $users = User::where('role', 'user')
            ->where('is_active', true)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->select(['id', 'name', 'email', 'device_type'])
            ->orderBy('name')
            ->get();
        
        $tokenStats = $this->firebaseService->getTokenStats();
        $isConfigured = $this->firebaseService->isConfigured();
        
        return view('admin.push-notifications.create', compact(
            'segments',
            'users',
            'tokenStats',
            'isConfigured'
        ));
    }
    
    /**
     * Send a new push notification
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'target_type' => 'required|in:all,specific,segment',
            'target_users' => 'required_if:target_type,specific|array',
            'target_users.*' => 'exists:users,id',
            'segment' => 'required_if:target_type,segment|in:premium,free,inactive,active,new',
            'image_url' => 'nullable|url',
            'action_url' => 'nullable|string|max:255',
            'send_in_app' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Prepare data
        $data = [];
        if ($request->action_url) {
            $data['action_url'] = $request->action_url;
        }
        if ($request->has('extra_data') && is_array($request->extra_data)) {
            $data = array_merge($data, $request->extra_data);
        }
        
        // Send the notification
        $pushNotification = $this->firebaseService->sendWithInAppNotification(
            targetType: $request->target_type,
            targetUsers: $request->target_users,
            segment: $request->segment,
            title: $request->title,
            body: $request->body,
            data: $data,
            imageUrl: $request->image_url,
            sentBy: auth()->id()
        );
        
        if ($pushNotification->status === 'completed') {
            return redirect()
                ->route('admin.push-notifications.show', $pushNotification)
                ->with('success', "Notification imetumwa kwa vifaa {$pushNotification->total_tokens}. Mafanikio: {$pushNotification->success_count}, Imeshindwa: {$pushNotification->failure_count}");
        }
        
        return redirect()
            ->route('admin.push-notifications.show', $pushNotification)
            ->with('error', 'Notification imeshindwa kutumwa. Angalia error details.');
    }
    
    /**
     * Show a specific push notification details
     */
    public function show(PushNotification $pushNotification)
    {
        $pushNotification->load('sender');
        
        // Get targeted users if specific
        $targetedUsers = null;
        if ($pushNotification->target_type === 'specific' && $pushNotification->target_users) {
            $targetedUsers = User::whereIn('id', $pushNotification->target_users)
                ->select(['id', 'name', 'email', 'device_type', 'fcm_token'])
                ->get();
        }
        
        return view('admin.push-notifications.show', compact(
            'pushNotification',
            'targetedUsers'
        ));
    }
    
    /**
     * Show FCM tokens management page
     */
    public function tokens(Request $request)
    {
        $search = $request->get('search');
        $deviceType = $request->get('device_type');
        
        $query = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->where('role', 'user');
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($deviceType) {
            $query->where('device_type', $deviceType);
        }
        
        $users = $query->latest('fcm_token_updated_at')
            ->paginate(20)
            ->appends($request->query());
        
        $tokenStats = $this->firebaseService->getTokenStats();
        
        return view('admin.push-notifications.tokens', compact('users', 'tokenStats'));
    }
    
    /**
     * Remove a user's FCM token
     */
    public function removeToken(User $user)
    {
        $user->update([
            'fcm_token' => null,
            'fcm_token_updated_at' => now(),
        ]);
        
        return back()->with('success', "FCM token ya {$user->name} imeondolewa.");
    }
    
    /**
     * Send test notification to a specific user
     */
    public function sendTest(Request $request, User $user)
    {
        if (!$user->fcm_token) {
            return back()->with('error', 'Mtumiaji hana FCM token.');
        }
        
        $result = $this->firebaseService->sendToDevice(
            fcmToken: $user->fcm_token,
            title: '🧪 Test Notification',
            body: 'Hii ni test notification kutoka SKYpesa Admin. Kama unaona hii, push notifications zinafanya kazi vizuri!',
            data: ['type' => 'test', 'action' => 'test']
        );
        
        if ($result['success']) {
            return back()->with('success', "Test notification imetumwa kwa {$user->name}.");
        }
        
        return back()->with('error', "Imeshindwa kutuma: " . ($result['error'] ?? 'Unknown error'));
    }
    
    /**
     * Resend a failed notification
     */
    public function resend(PushNotification $pushNotification)
    {
        if ($pushNotification->status !== 'failed') {
            return back()->with('error', 'Notification hii haijashindwa kutumwa.');
        }
        
        // Create new notification with same parameters
        $newNotification = $this->firebaseService->sendWithInAppNotification(
            targetType: $pushNotification->target_type,
            targetUsers: $pushNotification->target_users,
            segment: $pushNotification->segment,
            title: $pushNotification->title,
            body: $pushNotification->body,
            data: $pushNotification->data ?? [],
            imageUrl: $pushNotification->image_url,
            sentBy: auth()->id()
        );
        
        return redirect()
            ->route('admin.push-notifications.show', $newNotification)
            ->with('success', 'Notification imetumwa tena.');
    }
    
    /**
     * Delete a push notification record
     */
    public function destroy(PushNotification $pushNotification)
    {
        $pushNotification->delete();
        
        return redirect()
            ->route('admin.push-notifications.index')
            ->with('success', 'Push notification record imefutwa.');
    }
    
    /**
     * Get segment options with user counts
     */
    private function getSegmentOptions(): array
    {
        $baseQuery = fn() => User::where('role', 'user')
            ->where('is_active', true)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '');
        
        return [
            'all' => [
                'label' => 'Watumiaji Wote',
                'count' => $baseQuery()->count(),
            ],
            'premium' => [
                'label' => 'Premium (Paid Plans)',
                'count' => $baseQuery()
                    ->whereHas('activeSubscription.plan', function ($q) {
                        $q->where('price', '>', 0);
                    })
                    ->count(),
            ],
            'free' => [
                'label' => 'Free Plan',
                'count' => $baseQuery()
                    ->whereHas('activeSubscription.plan', function ($q) {
                        $q->where('price', 0);
                    })
                    ->count(),
            ],
            'active' => [
                'label' => 'Active (24h)',
                'count' => $baseQuery()
                    ->where('last_login_at', '>=', now()->subDay())
                    ->count(),
            ],
            'inactive' => [
                'label' => 'Inactive (7+ days)',
                'count' => $baseQuery()
                    ->where('last_login_at', '<', now()->subDays(7))
                    ->count(),
            ],
            'new' => [
                'label' => 'New Users (7 days)',
                'count' => $baseQuery()
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
            ],
        ];
    }
}
