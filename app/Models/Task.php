<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'url',
        'provider',
        'duration_seconds',
        'reward_override',
        'daily_limit',
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
        'requirements' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function completions()
    {
        return $this->hasMany(TaskCompletion::class);
    }

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
            ->count();
    }

    public function canUserComplete(User $user): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if ($this->daily_limit) {
            return $this->userCompletionsToday($user) < $this->daily_limit;
        }

        return true;
    }
}
