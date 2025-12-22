<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Link Pool Model
 * 
 * Manages pools of links for random rotation.
 * Example pools: SkyBoost™, SkyLinks™
 * 
 * When a user clicks a task from this pool, the system
 * randomly selects one active link from the pool.
 */
class LinkPool extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'reward_amount',
        'duration_seconds',
        'daily_user_limit',
        'daily_global_limit',
        'cooldown_seconds',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'duration_seconds' => 'integer',
        'daily_user_limit' => 'integer',
        'daily_global_limit' => 'integer',
        'cooldown_seconds' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all links in this pool
     */
    public function links(): HasMany
    {
        return $this->hasMany(PoolLink::class);
    }

    /**
     * Get only active links
     */
    public function activeLinks(): HasMany
    {
        return $this->hasMany(PoolLink::class)->where('is_active', true);
    }

    /**
     * Get a random active link from this pool
     */
    public function getRandomLink(): ?PoolLink
    {
        return $this->activeLinks()->inRandomOrder()->first();
    }

    /**
     * Get active pools ordered by sort order
     */
    public static function getActivePools()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Check if user can access this pool today
     */
    public function canUserAccess(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check daily user limit
        if ($this->daily_user_limit) {
            $todayCount = $user->taskCompletions()
                ->whereHas('task', fn($q) => $q->where('link_pool_id', $this->id))
                ->whereDate('created_at', today())
                ->count();
            
            if ($todayCount >= $this->daily_user_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get total clicks today
     */
    public function getTodayClicksAttribute(): int
    {
        return $this->links()->sum('clicks_today') ?? 0;
    }

    /**
     * Get total clicks all time
     */
    public function getTotalClicksAttribute(): int
    {
        return $this->links()->sum('total_clicks') ?? 0;
    }

    /**
     * Get reward for a specific user based on their subscription plan
     * 
     * NOTE: We use the USER'S PLAN reward rate, NOT the pool's reward_amount.
     * The pool's reward_amount is just for admin reference/display.
     * 
     * @param User $user
     * @return float The reward amount based on user's subscription plan
     */
    public function getRewardFor(User $user): float
    {
        // Always use the user's subscription plan reward rate
        return $user->getRewardPerTask();
    }

    /**
     * Get display reward (for UI, shows user's actual rate)
     */
    public function getDisplayRewardFor(User $user): string
    {
        return 'TZS ' . number_format($this->getRewardFor($user));
    }
}
