<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public static function getFree()
    {
        return static::where('name', 'free')->first();
    }

    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function hasUnlimitedTasks(): bool
    {
        return is_null($this->daily_task_limit);
    }
}
