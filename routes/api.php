<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SurveyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| SKYpesa API v1 - All endpoints for mobile app and third-party integrations
| Base URL: /api/v1
|
*/

Route::prefix('v1')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    
    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'message' => 'SKYpesa API is running',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
        ]);
    });
    
    // App Info
    Route::get('/info', function () {
        return response()->json([
            'app_name' => config('app.name'),
            'version' => '1.0.0',
            'min_app_version' => '1.0.0',
            'maintenance_mode' => app()->isDownForMaintenance(),
            'support_email' => 'support@skypesa.com',
            'support_phone' => '+255700000000',
        ]);
    });
    
    // Subscription Plans (Public)
    Route::get('/plans', [SubscriptionController::class, 'index']);
    
    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('auth')->group(function () {
        // Register
        Route::post('/register', [AuthController::class, 'register']);
        
        // Login
        Route::post('/login', [AuthController::class, 'login']);
        
        // Forgot Password
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        
        // Reset Password
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        
        // Verify Email
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
        
        // Resend Verification
        Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    });
    
    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Authentication Required)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // Logout
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        
        // Refresh Token
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        
        /*
        |--------------------------------------------------------------------------
        | User Profile
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('user')->group(function () {
            // Get Profile
            Route::get('/profile', [UserController::class, 'profile']);
            
            // Update Profile
            Route::put('/profile', [UserController::class, 'updateProfile']);
            
            // Update Avatar
            Route::post('/avatar', [UserController::class, 'updateAvatar']);
            
            // Change Password
            Route::put('/password', [UserController::class, 'changePassword']);
            
            // Get Dashboard Stats
            Route::get('/dashboard', [UserController::class, 'dashboard']);
            
            // Get Activity Summary
            Route::get('/activity', [UserController::class, 'activity']);
            
            // Delete Account
            Route::delete('/account', [UserController::class, 'deleteAccount']);
            
            // FCM Token for Push Notifications
            Route::post('/fcm-token', [UserController::class, 'updateFcmToken']);
        });
        
        /*
        |--------------------------------------------------------------------------
        | Tasks
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('tasks')->group(function () {
            // List Available Tasks
            Route::get('/', [TaskController::class, 'index']);
            
            // Get Single Task
            Route::get('/{task}', [TaskController::class, 'show']);
            
            // Start Task (Lock)
            Route::post('/{task}/start', [TaskController::class, 'start']);
            
            // Check Task Status
            Route::post('/{task}/status', [TaskController::class, 'status']);
            
            // Complete Task
            Route::post('/{task}/complete', [TaskController::class, 'complete']);
            
            // Cancel Task
            Route::post('/cancel', [TaskController::class, 'cancel']);
            
            // Get User's Active Task
            Route::get('/activity/current', [TaskController::class, 'activeTask']);
            
            // Get Task History
            Route::get('/history/completed', [TaskController::class, 'history']);
        });
        
        /*
        |--------------------------------------------------------------------------
        | Wallet & Transactions
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('wallet')->group(function () {
            // Get Wallet Balance & Info
            Route::get('/', [WalletController::class, 'index']);
            
            // Get Transaction History
            Route::get('/transactions', [WalletController::class, 'transactions']);
            
            // Get Single Transaction
            Route::get('/transactions/{transaction}', [WalletController::class, 'showTransaction']);
            
            // Get Earnings Summary
            Route::get('/earnings', [WalletController::class, 'earnings']);
        });
        
        /*
        |--------------------------------------------------------------------------
        | Withdrawals
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('withdrawals')->group(function () {
            // List User's Withdrawals
            Route::get('/', [WithdrawalController::class, 'index']);
            
            // Get Withdrawal Info (limits, fees)
            Route::get('/info', [WithdrawalController::class, 'info']);
            
            // Create Withdrawal Request
            Route::post('/', [WithdrawalController::class, 'store']);
            
            // Get Single Withdrawal
            Route::get('/{withdrawal}', [WithdrawalController::class, 'show']);
            
            // Cancel Pending Withdrawal
            Route::delete('/{withdrawal}', [WithdrawalController::class, 'cancel']);
        });
        
        /*
        |--------------------------------------------------------------------------
        | Subscriptions
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('subscriptions')->group(function () {
            // Get Current Subscription
            Route::get('/current', [SubscriptionController::class, 'current']);
            
            // Subscribe to Plan
            Route::post('/subscribe/{plan}', [SubscriptionController::class, 'subscribe']);
            
            // Initiate Payment
            Route::post('/pay/{plan}', [SubscriptionController::class, 'initiatePayment']);
            
            // Check Payment Status
            Route::get('/payment-status/{orderId}', [SubscriptionController::class, 'paymentStatus']);
            
            // Get Subscription History
            Route::get('/history', [SubscriptionController::class, 'history']);
        });
        
        /*
        |--------------------------------------------------------------------------
        | Referrals
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('referrals')->group(function () {
            // Get Referral Info & Link
            Route::get('/', [ReferralController::class, 'index']);
            
            // Get Referred Users
            Route::get('/users', [ReferralController::class, 'referredUsers']);
            
            // Get Referral Earnings
            Route::get('/earnings', [ReferralController::class, 'earnings']);
            
            // Get Referral Stats
            Route::get('/stats', [ReferralController::class, 'stats']);
        });
        
        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('notifications')->group(function () {
            // Get All Notifications
            Route::get('/', [NotificationController::class, 'index']);
            
            // Get Unread Count
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            
            // Mark as Read
            Route::put('/{notification}/read', [NotificationController::class, 'markAsRead']);
            
            // Mark All as Read
            Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
            
            // Delete Notification
            Route::delete('/{notification}', [NotificationController::class, 'destroy']);
        });
        
        /*
        |--------------------------------------------------------------------------
        | Surveys (BitLabs)
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('surveys')->group(function () {
            // Get Available Surveys
            Route::get('/', [SurveyController::class, 'index']);
            
            // Get Survey Statistics
            Route::get('/stats', [SurveyController::class, 'stats']);
            
            // Get Survey History
            Route::get('/history', [SurveyController::class, 'history']);
            
            // Demo Survey Complete (Testing only)
            Route::post('/demo/{id}/complete', [SurveyController::class, 'demoComplete'])->name('surveys.demo.complete');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Routes
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('webhooks')->group(function () {
        // ZenoPay Payment Callback
        Route::post('/zenopay', [SubscriptionController::class, 'zenoPayCallback'])->name('api.webhooks.zenopay');
        
        // Adsterra Postback
        Route::post('/adsterra', [TaskController::class, 'adsterraPostback'])->name('api.webhooks.adsterra');
        
        // Monetag Postback
        Route::post('/monetag', [TaskController::class, 'monetagPostback'])->name('api.webhooks.monetag');
        
        // BitLabs Survey Callback
        Route::match(['get', 'post'], '/bitlabs', [SurveyController::class, 'postback'])->name('api.webhooks.bitlabs');
        
        // 🧪 BitLabs Test Endpoint (Development Only)
        Route::get('/test-bitlabs-callback', function () {
            if (!app()->environment('local') && !config('bitlabs.demo_mode')) {
                abort(404);
            }
            
            $userId = request('user_id', 1);
            $tx = 'test_' . time() . '_' . rand(1000, 9999);
            
            // Build test callback data
            $testData = [
                'tx' => $tx,
                'user_id' => $userId,
                'survey_id' => 'test_survey_' . rand(1000, 9999),
                'loi' => request('loi', 5),
                'value' => request('value', 0.50),
                'status' => request('status', 'complete'),
                'ip' => request()->ip(),
            ];
            
            // Simulate BitLabs callback
            $bitLabsService = app(\App\Services\BitLabsService::class);
            $result = $bitLabsService->handleCallback($testData);
            
            return response()->json([
                'test_mode' => true,
                'message' => 'BitLabs Callback Test Simulated',
                'input' => $testData,
                'result' => $result,
                'user_wallet_after' => \App\Models\User::find($userId)?->wallet?->balance ?? 0,
            ]);
        })->name('api.webhooks.test-bitlabs');
    });
});

/*
|--------------------------------------------------------------------------
| Legacy API Routes (v0 - Deprecated)
|--------------------------------------------------------------------------
*/

Route::prefix('v0')->group(function () {
    Route::get('/status', function () {
        return response()->json([
            'warning' => 'API v0 is deprecated. Please upgrade to v1.',
            'status' => 'ok',
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Webhook Routes (Root Level - No Version Prefix)
|--------------------------------------------------------------------------
| These webhooks are at /api/webhooks/* for external services that
| don't support versioned URLs.
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')->group(function () {
    // BitLabs Survey Callback (Primary - used by BitLabs dashboard)
    Route::match(['get', 'post'], '/bitlabs', [\App\Http\Controllers\Api\SurveyController::class, 'postback'])
        ->name('bitlabs.callback');
    
    // ZenoPay Payment Callback
    Route::post('/zenopay', [\App\Http\Controllers\Api\SubscriptionController::class, 'zenoPayCallback'])
        ->name('zenopay.callback');
    
    // Adsterra Postback
    Route::match(['get', 'post'], '/adsterra', [\App\Http\Controllers\Api\TaskController::class, 'adsterraPostback'])
        ->name('adsterra.postback');
    
    // Monetag Postback
    Route::match(['get', 'post'], '/monetag', [\App\Http\Controllers\Api\TaskController::class, 'monetagPostback'])
        ->name('monetag.postback');
});

/*
|--------------------------------------------------------------------------
| Direct BitLabs Callback Route
|--------------------------------------------------------------------------
| This route is at /api/bitlabs for BitLabs dashboard
| Callback URL: https://skypesa.hosting.hollyn.online/api/bitlabs
|--------------------------------------------------------------------------
*/

Route::match(['get', 'post'], '/bitlabs', [\App\Http\Controllers\Api\SurveyController::class, 'postback'])
    ->name('bitlabs.callback.direct');

