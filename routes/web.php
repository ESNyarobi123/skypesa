<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminTaskController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Admin\AdminAdsterraController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminDirectLinkController;
use App\Http\Controllers\Admin\AdminLinkPoolController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\LanguageController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    $plans = \App\Models\SubscriptionPlan::where('is_active', true)
        ->orderBy('sort_order')
        ->get();
    return view('welcome', compact('plans'));
})->name('home');

// Language switching route (accessible to all users)
Route::get('/language/{locale}', [LanguageController::class, 'switch'])
    ->where('locale', 'en|sw')
    ->name('language.switch');

// Public Pages (FAQ, Contact, Terms, Privacy)
Route::get('/faq', [App\Http\Controllers\PageController::class, 'faq'])->name('pages.faq');
Route::get('/contact', [App\Http\Controllers\PageController::class, 'contact'])->name('pages.contact');
Route::get('/terms', [App\Http\Controllers\PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy', [App\Http\Controllers\PageController::class, 'privacy'])->name('pages.privacy');

/*
|--------------------------------------------------------------------------
| Task Redirect Routes (Click Tracking)
|--------------------------------------------------------------------------
| These routes handle redirects to ad providers with tracking.
| Since Monetag Direct Links and Adsterra Smartlink don't have postbacks,
| we track clicks here and use timer-based completion.
|
| Anti-fraud: Logged + limits applied before redirect
*/
Route::prefix('go')->name('go.')->middleware('auth')->group(function () {
    // Monetag Direct Links (by slug: immortal, glad, etc.)
    Route::get('/monetag/{slug}', [App\Http\Controllers\GoController::class, 'monetag'])
        ->where('slug', '[a-z_]+')
        ->name('monetag');
    
    // Adsterra Smartlink
    Route::get('/adsterra/{task?}', [App\Http\Controllers\GoController::class, 'adsterra'])
        ->name('adsterra');
    
    // Generic redirect (for flexibility)
    Route::get('/{provider}/{slug}', [App\Http\Controllers\GoController::class, 'redirect'])
        ->name('provider');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendOtp'])->name('password.email');
    Route::get('/password/otp', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showOtpForm'])->name('password.otp');
    Route::post('/password/otp', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::get('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'resetPassword'])->name('password.reset.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/activity-status', [TaskController::class, 'activityStatus'])->name('tasks.activity-status');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');
    Route::post('/tasks/{task}/status', [TaskController::class, 'status'])->name('tasks.status');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('/tasks/cancel', [TaskController::class, 'cancel'])->name('tasks.cancel');
    
    // Click Fraud Detection (Web)
    Route::post('/tasks/{task}/report-click', function (\App\Models\Task $task, \Illuminate\Http\Request $request) {
        $user = auth()->user();
        
        // Check if user is blocked
        if ($user->isBlocked()) {
            return response()->json([
                'status' => 'blocked',
                'message' => 'Akaunti yako imezuiwa. Wasiliana na admin.',
                'is_blocked' => true,
            ], 403);
        }
        
        // Record the click flag
        $flag = \App\Models\UserClickFlag::recordClick(
            user: $user,
            task: $task,
            completion: null,
            clickCount: $request->input('click_count', 1),
            data: [
                'ip_address' => $request->ip(),
                'device_info' => $request->input('device_info', $request->userAgent()),
                'click_coordinates' => $request->input('click_coordinates'),
                'notes' => $request->input('notes', 'Web click'),
            ]
        );
        
        // Refresh to check if auto-blocked
        $user->refresh();
        
        if ($user->isBlocked()) {
            return response()->json([
                'status' => 'blocked',
                'message' => 'Akaunti yako imezuiwa kwa sababu ya shughuli za tuhuma.',
                'is_blocked' => true,
            ], 403);
        }
        
        return response()->json([
            'status' => 'recorded',
            'flag_id' => $flag->id,
            'total_flagged_clicks' => $user->total_flagged_clicks,
            'threshold' => \App\Models\UserClickFlag::AUTO_BLOCK_THRESHOLD,
            'remaining_before_block' => max(0, \App\Models\UserClickFlag::AUTO_BLOCK_THRESHOLD - $user->total_flagged_clicks),
        ]);
    })->name('tasks.report-click');
    
    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    
    // Withdrawals
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/create', [WithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
    
    // Subscriptions
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/{plan}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::get('/subscriptions/{plan}/payment', [SubscriptionController::class, 'showPayment'])->name('subscriptions.payment');
    Route::post('/subscriptions/{plan}/process', [SubscriptionController::class, 'processPayment'])->name('subscriptions.process');
    
    // Referrals
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    
    // Gamification Routes
    Route::get('/leaderboard', [App\Http\Controllers\GamificationController::class, 'leaderboard'])->name('leaderboard');
    Route::post('/daily-goal/claim', [App\Http\Controllers\GamificationController::class, 'claimDailyGoal'])->name('daily-goal.claim');
    Route::get('/api/daily-goal', [App\Http\Controllers\GamificationController::class, 'getDailyGoal'])->name('api.daily-goal');
    Route::get('/api/leaderboard', [App\Http\Controllers\GamificationController::class, 'getLeaderboardData'])->name('api.leaderboard');
    
    // Support routes
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::get('/support/create', [SupportController::class, 'create'])->name('support.create');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    Route::get('/support/{id}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply');

    // Announcements (dismiss popup)
    Route::post('/announcements/{announcement}/dismiss', function (\App\Models\Announcement $announcement) {
        $user = auth()->user();
        if ($announcement->isCurrentlyActive()) {
            $announcement->recordView($user);
        }
        return response()->json(['success' => true]);
    })->name('announcements.dismiss');

    // Payments (ZenoPay)
    Route::get('/pay/subscription/{plan}', [PaymentController::class, 'subscriptionPayment'])->name('payments.subscription');
    Route::post('/pay/subscription/{plan}', [PaymentController::class, 'initiateSubscriptionPayment'])->name('payments.subscription.initiate');
    Route::get('/pay/deposit', [PaymentController::class, 'depositPage'])->name('payments.deposit');
    Route::post('/pay/deposit', [PaymentController::class, 'initiateDeposit'])->name('payments.deposit.initiate');
    Route::get('/pay/status', [PaymentController::class, 'checkPaymentStatus'])->name('payments.status');
    
    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');
        Route::get('/live-stats', [AdminDashboardController::class, 'liveStats'])->name('live-stats');
        Route::get('/referrals', [AdminDashboardController::class, 'referrals'])->name('referrals');
        Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('transactions');
        
        // Users Management (Full CRUD)
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/adjust-balance', [AdminUserController::class, 'adjustBalance'])->name('users.adjust-balance');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        
        // Subscription Plans Management
        Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [AdminPlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [AdminPlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [AdminPlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [AdminPlanController::class, 'destroy'])->name('plans.destroy');
        Route::patch('/plans/{plan}/toggle-status', [AdminPlanController::class, 'toggleStatus'])->name('plans.toggle-status');
        Route::post('/plans/reorder', [AdminPlanController::class, 'reorder'])->name('plans.reorder');
        
        // Direct Links / Ads Management
        Route::get('/directlinks', [AdminDirectLinkController::class, 'index'])->name('directlinks.index');
        Route::get('/directlinks/create', [AdminDirectLinkController::class, 'create'])->name('directlinks.create');
        Route::post('/directlinks', [AdminDirectLinkController::class, 'store'])->name('directlinks.store');
        Route::get('/directlinks/{directlink}/edit', [AdminDirectLinkController::class, 'edit'])->name('directlinks.edit');
        Route::put('/directlinks/{directlink}', [AdminDirectLinkController::class, 'update'])->name('directlinks.update');
        Route::delete('/directlinks/{directlink}', [AdminDirectLinkController::class, 'destroy'])->name('directlinks.destroy');
        Route::patch('/directlinks/{directlink}/toggle-status', [AdminDirectLinkController::class, 'toggleStatus'])->name('directlinks.toggle-status');
        Route::post('/directlinks/{directlink}/duplicate', [AdminDirectLinkController::class, 'duplicate'])->name('directlinks.duplicate');
        Route::get('/directlinks/{directlink}/analytics', [AdminDirectLinkController::class, 'analytics'])->name('directlinks.analytics');
        Route::post('/directlinks/bulk-toggle', [AdminDirectLinkController::class, 'bulkToggle'])->name('directlinks.bulk-toggle');
        
        // Tasks (existing - for compatibility)
        Route::resource('tasks', AdminTaskController::class)->except(['show']);
        
        // Withdrawals
        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('/withdrawals/export', [AdminWithdrawalController::class, 'export'])->name('withdrawals.export');
        Route::patch('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::patch('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
        Route::patch('/withdrawals/{withdrawal}/mark-paid', [AdminWithdrawalController::class, 'markPaid'])->name('withdrawals.mark-paid');
        Route::post('/withdrawals/bulk-approve', [AdminWithdrawalController::class, 'bulkApprove'])->name('withdrawals.bulk-approve');
        Route::post('/withdrawals/bulk-reject', [AdminWithdrawalController::class, 'bulkReject'])->name('withdrawals.bulk-reject');
        
        // Settings Management
        Route::get('/settings', [App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/clear-cache', [App\Http\Controllers\Admin\AdminSettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('/settings/reset-demo', [App\Http\Controllers\Admin\AdminSettingsController::class, 'resetDemoData'])->name('settings.reset-demo');
        
        // Adsterra Integration
        Route::get('/adsterra', [AdminAdsterraController::class, 'index'])->name('adsterra.index');
        Route::post('/adsterra/refresh', [AdminAdsterraController::class, 'refresh'])->name('adsterra.refresh');
        Route::post('/adsterra/import-placement', [AdminAdsterraController::class, 'importPlacement'])->name('adsterra.import-placement');
        Route::post('/adsterra/import-all', [AdminAdsterraController::class, 'importAll'])->name('adsterra.import-all');
        Route::post('/adsterra/sync', [AdminAdsterraController::class, 'sync'])->name('adsterra.sync');

        // Link Pools Management (SkyBoost™, SkyLinks™)
        Route::get('/linkpools', [AdminLinkPoolController::class, 'index'])->name('linkpools.index');
        Route::get('/linkpools/create', [AdminLinkPoolController::class, 'create'])->name('linkpools.create');
        Route::post('/linkpools', [AdminLinkPoolController::class, 'store'])->name('linkpools.store');
        Route::get('/linkpools/{linkpool}', [AdminLinkPoolController::class, 'show'])->name('linkpools.show');
        Route::get('/linkpools/{linkpool}/edit', [AdminLinkPoolController::class, 'edit'])->name('linkpools.edit');
        Route::put('/linkpools/{linkpool}', [AdminLinkPoolController::class, 'update'])->name('linkpools.update');
        Route::delete('/linkpools/{linkpool}', [AdminLinkPoolController::class, 'destroy'])->name('linkpools.destroy');
        Route::patch('/linkpools/{linkpool}/toggle-status', [AdminLinkPoolController::class, 'toggleStatus'])->name('linkpools.toggle-status');
        
        // Pool Links (nested under pools)
        Route::get('/linkpools/{linkpool}/links/create', [AdminLinkPoolController::class, 'createLink'])->name('linkpools.links.create');
        Route::post('/linkpools/{linkpool}/links', [AdminLinkPoolController::class, 'storeLink'])->name('linkpools.links.store');
        Route::get('/linkpools/{linkpool}/links/{link}/edit', [AdminLinkPoolController::class, 'editLink'])->name('linkpools.links.edit');
        Route::put('/linkpools/{linkpool}/links/{link}', [AdminLinkPoolController::class, 'updateLink'])->name('linkpools.links.update');
        Route::delete('/linkpools/{linkpool}/links/{link}', [AdminLinkPoolController::class, 'destroyLink'])->name('linkpools.links.destroy');
        Route::patch('/linkpools/{linkpool}/links/{link}/toggle-status', [AdminLinkPoolController::class, 'toggleLinkStatus'])->name('linkpools.links.toggle-status');
        Route::post('/linkpools/{linkpool}/links/{link}/reset-clicks', [AdminLinkPoolController::class, 'resetLinkClicks'])->name('linkpools.links.reset-clicks');
        Route::post('/linkpools/{linkpool}/links/bulk-import', [AdminLinkPoolController::class, 'bulkImportLinks'])->name('linkpools.links.bulk-import');

        // Announcements (Broadcast to Users)
        Route::get('/announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::patch('/announcements/{announcement}/toggle', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'toggleActive'])->name('announcements.toggle');
        Route::get('/announcements/{announcement}/stats', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'stats'])->name('announcements.stats');

        // Support Management
        Route::get('/support', [App\Http\Controllers\Admin\AdminSupportController::class, 'index'])->name('support.index');
        Route::get('/support/{id}', [App\Http\Controllers\Admin\AdminSupportController::class, 'show'])->name('support.show');
        Route::post('/support/{id}/reply', [App\Http\Controllers\Admin\AdminSupportController::class, 'reply'])->name('support.reply');
        Route::patch('/support/{id}/close', [App\Http\Controllers\Admin\AdminSupportController::class, 'close'])->name('support.close');

        // Blocked Users & Fraud Detection
        Route::get('/blocked-users', [App\Http\Controllers\Admin\AdminBlockedUserController::class, 'index'])->name('blocked-users.index');
        Route::get('/blocked-users/{user}', [App\Http\Controllers\Admin\AdminBlockedUserController::class, 'show'])->name('blocked-users.show');
        Route::post('/blocked-users/{user}/block', [App\Http\Controllers\Admin\AdminBlockedUserController::class, 'block'])->name('blocked-users.block');
        Route::post('/blocked-users/{user}/unblock', [App\Http\Controllers\Admin\AdminBlockedUserController::class, 'unblock'])->name('blocked-users.unblock');
        Route::post('/blocked-users/{user}/reset-clicks', [App\Http\Controllers\Admin\AdminBlockedUserController::class, 'resetClickCount'])->name('blocked-users.reset-clicks');
        Route::post('/blocked-users/{user}/review-all', [App\Http\Controllers\Admin\AdminBlockedUserController::class, 'reviewAllFlags'])->name('blocked-users.review-all');
        Route::post('/blocked-users/flags/{flag}/review', [App\Http\Controllers\Admin\AdminBlockedUserController::class, 'reviewFlag'])->name('blocked-users.review-flag');
        Route::get('/blocked-users-api/recent-flags', [App\Http\Controllers\Admin\AdminBlockedUserController::class, 'recentFlags'])->name('blocked-users.recent-flags');

    });

    // User Blocked Page (shown when user is blocked)
    Route::get('/blocked', function () {
        $user = auth()->user();
        if (!$user->isBlocked()) {
            return redirect()->route('dashboard');
        }
        return view('user.blocked', compact('user'));
    })->name('user.blocked');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
});
