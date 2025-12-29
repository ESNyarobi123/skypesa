<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'data',
        'image_url',
        'target_type',
        'target_users',
        'segment',
        'total_tokens',
        'success_count',
        'failure_count',
        'error_details',
        'status',
        'sent_by',
        'sent_at',
        'completed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'target_users' => 'array',
        'error_details' => 'array',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the admin who sent this notification
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Scope for pending notifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed notifications
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_tokens === 0) {
            return 0;
        }
        return round(($this->success_count / $this->total_tokens) * 100, 2);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => '#f59e0b',
            'sending' => '#3b82f6',
            'completed' => '#10b981',
            'failed' => '#ef4444',
            default => '#6b7280',
        };
    }

    /**
     * Get status label in Swahili
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Inasubiri',
            'sending' => 'Inatuma',
            'completed' => 'Imekamilika',
            'failed' => 'Imeshindwa',
            default => 'Haijulikani',
        };
    }

    /**
     * Get target type label
     */
    public function getTargetTypeLabelAttribute(): string
    {
        return match($this->target_type) {
            'all' => 'Watumiaji Wote',
            'specific' => 'Watumiaji Maalum',
            'segment' => 'Segment: ' . ucfirst($this->segment ?? 'All'),
            default => 'Unknown',
        };
    }
}
