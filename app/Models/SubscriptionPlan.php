<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'display_name',
        'description',
        'price',
        'duration_days',
        'daily_task_limit',
        'reward_per_task',
        'min_withdrawal',
        'withdrawal_fee_percent',
        'processing_days',
        'badge_color',
        'icon',
        'features',
        'sort_order',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'reward_per_task' => 'decimal:2',
        'min_withdrawal' => 'decimal:2',
        'withdrawal_fee_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'features' => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_featured' => false,
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public static function getFree()
    {
        return static::where('slug', 'free')->orWhere('name', 'free')->first();
    }

    public static function getBySlug(string $slug)
    {
        return static::where('slug', $slug)->first();
    }

    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function isPaid(): bool
    {
        return $this->price > 0;
    }

    public function hasUnlimitedTasks(): bool
    {
        return is_null($this->daily_task_limit);
    }

    public function getDailyEarningsEstimate(): float
    {
        if ($this->hasUnlimitedTasks()) {
            return $this->reward_per_task * 50; // Estimate 50 tasks for unlimited
        }
        return $this->reward_per_task * $this->daily_task_limit;
    }

    public function getMonthlyEarningsEstimate(): float
    {
        return $this->getDailyEarningsEstimate() * 30;
    }

    /**
     * Get formatted features list
     */
    public function getFormattedFeatures(): array
    {
        if (!empty($this->features)) {
            return $this->features;
        }

        // Generate default features based on plan attributes
        $features = [];

        if ($this->hasUnlimitedTasks()) {
            $features[] = 'Tasks zisizo na kikomo kwa siku';
        } else {
            $features[] = "Tasks {$this->daily_task_limit} kwa siku";
        }

        $features[] = "TZS " . number_format($this->reward_per_task, 0) . " kwa kila task";
        $features[] = "Minimum withdrawal: TZS " . number_format($this->min_withdrawal, 0);
        $features[] = "Ada ya kutoa: {$this->withdrawal_fee_percent}%";
        $features[] = "Muda wa kuchakata: Siku {$this->processing_days}";

        return $features;
    }

    /**
     * Get plan badge/label color
     */
    public function getBadgeClass(): string
    {
        return match($this->slug ?? $this->name) {
            'free' => 'bg-gray-500',
            'silver', 'phase1' => 'bg-slate-400',
            'gold', 'phase2' => 'bg-yellow-500',
            'vip', 'premium' => 'bg-purple-600',
            default => 'bg-primary',
        };
    }

    /**
     * Get plan icon name
     */
    public function getIconName(): string
    {
        return $this->icon ?? match($this->slug ?? $this->name) {
            'free' => 'gift',
            'silver', 'phase1' => 'star',
            'gold', 'phase2' => 'trophy',
            'vip', 'premium' => 'crown',
            default => 'sparkles',
        };
    }
}

