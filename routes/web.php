<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminTaskController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Admin\AdminAdsterraController;
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
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    
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
    
    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        // Tasks
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
    });
});
