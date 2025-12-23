<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'is_active',
        'is_verified',
        'referral_code',
        'referred_by',
        'device_fingerprint',
        'last_login_at',
        'last_login_ip',
        // Fraud tracking fields
        'fraud_score',
        'flagged_tasks',
        'is_suspicious',
        'last_fraud_check',
        // Gamification fields
        'received_welcome_bonus',
        'first_task_completed',
        'first_task_at',
        'last_daily_goal_date',
        'daily_goal_progress',
        'daily_goal_claimed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'is_suspicious' => 'boolean',
            'last_login_at' => 'datetime',
            'last_fraud_check' => 'datetime',
            // Gamification casts
            'received_welcome_bonus' => 'boolean',
            'first_task_completed' => 'boolean',
            'first_task_at' => 'datetime',
            'last_daily_goal_date' => 'date',
            'daily_goal_claimed' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(Str::random(8));
            }
        });

        static::created(function ($user) {
            // Create wallet for new user
            $user->wallet()->create(['balance' => 0]);
            
            // Assign free subscription
            $freePlan = SubscriptionPlan::getFree();
            if ($freePlan) {
                $user->subscriptions()->create([
                    'plan_id' => $freePlan->id,
                    'starts_at' => now(),
                    'expires_at' => null,
                    'status' => 'active',
                ]);
            }
        });
    }

    // Relationships
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)->active()->latest();
    }

    public function taskCompletions()
    {
        return $this->hasMany(TaskCompletion::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    // Helper Methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function getCurrentPlan(): ?SubscriptionPlan
    {
        return $this->activeSubscription?->plan;
    }

    public function getPlanName(): string
    {
        return $this->getCurrentPlan()?->display_name ?? 'Free';
    }

    public function getDailyTaskLimit(): ?int
    {
        return $this->getCurrentPlan()?->daily_task_limit;
    }

    public function getRewardPerTask(): float
    {
        return $this->getCurrentPlan()?->reward_per_task ?? 3; // Default TZS 3 (profitable!)
    }

    public function getMinWithdrawal(): float
    {
        return $this->getCurrentPlan()?->min_withdrawal ?? 10000;
    }

    public function getWithdrawalFeePercent(): float
    {
        return $this->getCurrentPlan()?->withdrawal_fee_percent ?? 20;
    }

    public function tasksCompletedToday(): int
    {
        return $this->taskCompletions()
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();
    }

    public function canCompleteMoreTasks(): bool
    {
        $limit = $this->getDailyTaskLimit();
        
        if (is_null($limit)) {
            return true; // Unlimited
        }

        return $this->tasksCompletedToday() < $limit;
    }

    public function remainingTasksToday(): ?int
    {
        $limit = $this->getDailyTaskLimit();
        
        if (is_null($limit)) {
            return null; // Unlimited
        }

        return max(0, $limit - $this->tasksCompletedToday());
    }

    public function earningsToday(): float
    {
        return $this->taskCompletions()
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('reward_earned');
    }

    public function earningsThisMonth(): float
    {
        return $this->taskCompletions()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('reward_earned');
    }

    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=10b981&color=fff';
    }

    public function recordLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }
}
