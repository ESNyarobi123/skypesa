<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminTaskController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Admin\AdminAdsterraController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminDirectLinkController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
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
    
    // Surveys
    Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');
    Route::get('/surveys/history', [SurveyController::class, 'history'])->name('surveys.history');
    Route::post('/surveys/demo/{id}/complete', [SurveyController::class, 'demoComplete'])->name('surveys.demo.complete');
    
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
        Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
        
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
        Route::patch('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::patch('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
        Route::patch('/withdrawals/{withdrawal}/mark-paid', [AdminWithdrawalController::class, 'markPaid'])->name('withdrawals.mark-paid');
        
        // Adsterra Integration
        Route::get('/adsterra', [AdminAdsterraController::class, 'index'])->name('adsterra.index');
        Route::post('/adsterra/refresh', [AdminAdsterraController::class, 'refresh'])->name('adsterra.refresh');
        Route::post('/adsterra/import-placement', [AdminAdsterraController::class, 'importPlacement'])->name('adsterra.import-placement');
        Route::post('/adsterra/import-all', [AdminAdsterraController::class, 'importAll'])->name('adsterra.import-all');
        Route::post('/adsterra/sync', [AdminAdsterraController::class, 'sync'])->name('adsterra.sync');
        
        // Surveys (BitLabs)
        Route::get('/surveys', [AdminSurveyController::class, 'index'])->name('surveys.index');
        Route::get('/surveys/settings', [AdminSurveyController::class, 'settings'])->name('surveys.settings');
        Route::get('/surveys/analytics', [AdminSurveyController::class, 'analytics'])->name('surveys.analytics');
        Route::get('/surveys/{completion}', [AdminSurveyController::class, 'show'])->name('surveys.show');
        Route::post('/surveys/{completion}/credit', [AdminSurveyController::class, 'credit'])->name('surveys.credit');
        Route::post('/surveys/{completion}/reject', [AdminSurveyController::class, 'reject'])->name('surveys.reject');
        Route::post('/surveys/{completion}/reverse', [AdminSurveyController::class, 'reverse'])->name('surveys.reverse');
    });
});
