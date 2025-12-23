<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCompletion extends Model
{
    use HasFactory;

    /**
     * Completion status constants
     */
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FRAUD = 'fraud';

    protected $fillable = [
        'user_id',
        'task_id',
        'pool_link_id', // NEW: Track which pool link was used (for random rotation)
        'used_url',     // NEW: The actual URL that was displayed
        'reward_earned',
        'duration_spent',
        'required_duration',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'metadata',
        'status',
        'rejection_reason',
        'started_at',
        'completed_at',
        'is_locked',
        'lock_token',
        // New provider tracking fields
        'provider_ref',
        'provider_payout',
        'provider',
        'from_postback',
        'postback_received_at',
    ];

    protected $casts = [
        'reward_earned' => 'decimal:2',
        'provider_payout' => 'decimal:4',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'postback_received_at' => 'datetime',
        'is_locked' => 'boolean',
        'from_postback' => 'boolean',
    ];

    protected $attributes = [
        'from_postback' => false,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    /**
     * Get the pool link that was used for this completion
     * Only set if task uses a link pool (SkyBoost™, SkyLinks™, etc.)
     */
    public function poolLink()
    {
        return $this->belongsTo(PoolLink::class);
    }

    // ==========================================
    // Status Scopes
    // ==========================================

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFraud($query)
    {
        return $query->where('status', self::STATUS_FRAUD);
    }

    // ==========================================
    // Time-based Scopes
    // ==========================================

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // ==========================================
    // Provider Scopes
    // ==========================================

    public function scopeFromProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeFromPostback($query)
    {
        return $query->where('from_postback', true);
    }

    // ==========================================
    // Status Helpers
    // ==========================================

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFraud(): bool
    {
        return $this->status === self::STATUS_FRAUD;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Mark completion as completed via postback
     */
    public function markCompletedViaPostback(float $reward, string $providerRef, float $providerPayout = 0): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'reward_earned' => $reward,
            'provider_ref' => $providerRef,
            'provider_payout' => $providerPayout,
            'from_postback' => true,
            'postback_received_at' => now(),
        ]);
    }

    /**
     * Mark as fraud
     */
    public function markAsFraud(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_FRAUD,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Check if this completion was paid from postback
     */
    public function wasPaidFromPostback(): bool
    {
        return $this->from_postback && $this->isCompleted() && $this->reward_earned > 0;
    }
}
