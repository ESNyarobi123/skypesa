<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserClickFlag extends Model
{
    use HasFactory;

    /**
     * Default auto-block threshold (fallback if setting not configured)
     */
    public const DEFAULT_AUTO_BLOCK_THRESHOLD = 20;

    /**
     * Get the current auto-block threshold from settings
     */
    public static function getAutoBlockThreshold(): int
    {
        return (int) Setting::get('fraud_auto_block_threshold', self::DEFAULT_AUTO_BLOCK_THRESHOLD);
    }

    protected $fillable = [
        'user_id',
        'task_id',
        'task_completion_id',
        'click_count',
        'ip_address',
        'device_info',
        'click_coordinates',
        'notes',
        'is_reviewed',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'click_coordinates' => 'array',
        'is_reviewed' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user who made the suspicious click
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task that was flagged
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the task completion record
     */
    public function taskCompletion(): BelongsTo
    {
        return $this->belongsTo(TaskCompletion::class);
    }

    /**
     * Get the admin who reviewed this flag
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope for unreviewed flags
     */
    public function scopeUnreviewed($query)
    {
        return $query->where('is_reviewed', false);
    }

    /**
     * Scope for reviewed flags
     */
    public function scopeReviewed($query)
    {
        return $query->where('is_reviewed', true);
    }

    /**
     * Scope for today's flags
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Mark this flag as reviewed
     */
    public function markAsReviewed(User $admin): void
    {
        $this->update([
            'is_reviewed' => true,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Record a new click flag and check for auto-block
     * 
     * @param User $user
     * @param Task|null $task
     * @param TaskCompletion|null $completion
     * @param int $clickCount
     * @param array $data Additional data (ip_address, device_info, click_coordinates)
     * @return self
     */
    public static function recordClick(
        User $user,
        ?Task $task = null,
        ?TaskCompletion $completion = null,
        int $clickCount = 1,
        array $data = []
    ): self {
        // Create the flag record
        $flag = self::create([
            'user_id' => $user->id,
            'task_id' => $task?->id,
            'task_completion_id' => $completion?->id,
            'click_count' => $clickCount,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'device_info' => $data['device_info'] ?? request()->userAgent(),
            'click_coordinates' => $data['click_coordinates'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Increment user's total flagged clicks
        $user->increment('total_flagged_clicks');

        // Check for auto-block threshold
        $threshold = self::getAutoBlockThreshold();
        if ($user->fresh()->total_flagged_clicks >= $threshold) {
            $user->blockUser(
                reason: 'Auto-blocked: Exceeded suspicious click threshold (' . $threshold . ' tasks)',
                blockedBy: null // null = system auto-block
            );
        }

        return $flag;
    }

    /**
     * Get summary stats for a user
     */
    public static function getUserStats(User $user): array
    {
        $flags = self::where('user_id', $user->id);

        return [
            'total_flags' => $flags->count(),
            'total_clicks' => $flags->sum('click_count'),
            'unreviewed_flags' => $flags->unreviewed()->count(),
            'today_flags' => self::where('user_id', $user->id)->today()->count(),
            'threshold' => self::getAutoBlockThreshold(),
            'remaining_before_block' => max(0, self::getAutoBlockThreshold() - $user->total_flagged_clicks),
        ];
    }
}
