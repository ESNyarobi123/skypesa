<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    /**
     * Task categories
     */
    public const CATEGORY_TRAFFIC = 'traffic_task';
    public const CATEGORY_CONVERSION = 'conversion_task';

    protected $fillable = [
        'title',
        'description',
        'type',
        'category',
        'require_postback',
        'url',
        'link_pool_id', // NEW: Link to a pool for random link selection
        'provider',
        'duration_seconds',
        'cooldown_seconds',
        'reward_override',
        'min_payout',
        'daily_limit',
        'ip_daily_limit',
        'total_limit',
        'completions_count',
        'thumbnail',
        'icon',
        'requirements',
        'is_active',
        'is_featured',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'reward_override' => 'decimal:2',
        'min_payout' => 'decimal:4',
        'requirements' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'require_postback' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected $attributes = [
        'category' => 'traffic_task',
        'require_postback' => false,
        'cooldown_seconds' => 60,
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function completions()
    {
        return $this->hasMany(TaskCompletion::class);
    }

    /**
     * Get the link pool this task uses (for random link selection)
     */
    public function linkPool(): BelongsTo
    {
        return $this->belongsTo(LinkPool::class);
    }

    // ==========================================
    // LINK POOL METHODS
    // ==========================================

    /**
     * Check if this task uses a link pool
     */
    public function usesLinkPool(): bool
    {
        return !is_null($this->link_pool_id);
    }

    /**
     * Get a random link from the pool
     * Returns null if no pool or no active links
     */
    public function getRandomPoolLink(): ?PoolLink
    {
        if (!$this->usesLinkPool()) {
            return null;
        }

        // Pass the task's provider to the pool to prioritize matching links
        return $this->linkPool?->getRandomLink($this->provider);
    }

    /**
     * Get the URL for this task
     * If task uses a pool, returns a random link from pool
     * Otherwise returns the static URL
     */
    public function getTaskUrl(): string
    {
        if ($this->usesLinkPool()) {
            $poolLink = $this->getRandomPoolLink();
            return $poolLink?->url ?? $this->url ?? '#';
        }

        return $this->url ?? '#';
    }

    /**
     * Check if task has available links
     * For pool tasks, checks if pool has active links
     */
    public function hasAvailableLinks(): bool
    {
        if ($this->usesLinkPool()) {
            return $this->linkPool?->activeLinks()->count() > 0;
        }

        return !empty($this->url);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('total_limit')
                    ->orWhereColumn('completions_count', '<', 'total_limit');
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->total_limit && $this->completions_count >= $this->total_limit) {
            return false;
        }

        return true;
    }

    public function getRewardFor(User $user): float
    {
        if ($this->reward_override) {
            return $this->reward_override;
        }

        return $user->activeSubscription?->plan?->reward_per_task ?? 50;
    }

    public function userCompletionsToday(User $user): int
    {
        return $this->completions()
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();
    }

    public function canUserComplete(User $user): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if ($this->daily_limit) {
            if ($this->userCompletionsToday($user) >= $this->daily_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if IP has exceeded daily limit for this task
     */
    public function ipCompletionsToday(string $ip): int
    {
        return $this->completions()
            ->where('ip_address', $ip)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Check if IP can complete this task
     */
    public function canIPComplete(string $ip): bool
    {
        if (!$this->ip_daily_limit) {
            return true; // No IP limit set
        }

        return $this->ipCompletionsToday($ip) < $this->ip_daily_limit;
    }

    /**
     * Check if user is within cooldown period
     */
    public function isUserInCooldown(User $user): bool
    {
        $lastCompletion = $this->completions()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastCompletion) {
            return false;
        }

        $cooldown = $this->cooldown_seconds ?? 60;
        $cooldownEnds = $lastCompletion->created_at->addSeconds($cooldown);

        return now()->lt($cooldownEnds);
    }

    /**
     * Get remaining cooldown seconds for user
     */
    public function getCooldownRemaining(User $user): int
    {
        $lastCompletion = $this->completions()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastCompletion) {
            return 0;
        }

        $cooldown = $this->cooldown_seconds ?? 60;
        $cooldownEnds = $lastCompletion->created_at->addSeconds($cooldown);

        return max(0, $cooldownEnds->diffInSeconds(now(), false) * -1);
    }

    /**
     * Scope for traffic tasks (smaller rewards, strict limits)
     */
    public function scopeTrafficTasks($query)
    {
        return $query->where('category', self::CATEGORY_TRAFFIC);
    }

    /**
     * Scope for conversion tasks (bigger rewards, postback required)
     */
    public function scopeConversionTasks($query)
    {
        return $query->where('category', self::CATEGORY_CONVERSION);
    }

    /**
     * Check if this is a traffic task
     */
    public function isTrafficTask(): bool
    {
        return $this->category === self::CATEGORY_TRAFFIC;
    }

    /**
     * Check if this is a conversion task
     */
    public function isConversionTask(): bool
    {
        return $this->category === self::CATEGORY_CONVERSION;
    }

    /**
     * Check if payout requires postback confirmation
     */
    public function requiresPostback(): bool
    {
        // Conversion tasks always require postback
        if ($this->isConversionTask()) {
            return true;
        }

        return $this->require_postback ?? false;
    }
}
