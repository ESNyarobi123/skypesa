<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
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
    ];

    protected $casts = [
        'reward_earned' => 'decimal:2',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_locked' => 'boolean',
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

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
}
