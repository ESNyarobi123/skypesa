<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    /**
     * Get icon name based on type
     */
    public function getIconName(): string
    {
        return match($this->type) {
            'referral' => 'users',
            'task' => 'check-circle',
            'bonus' => 'gift',
            'withdrawal' => 'banknote',
            'subscription' => 'crown',
            'warning' => 'alert-triangle',
            'urgent' => 'alert-circle',
            'success' => 'check-circle',
            default => 'bell',
        };
    }

    /**
     * Get color based on type
     */
    public function getColor(): string
    {
        return match($this->type) {
            'referral' => '#8b5cf6',
            'task' => '#10b981',
            'bonus' => '#f59e0b',
            'withdrawal' => '#3b82f6',
            'subscription' => '#ec4899',
            'warning' => '#f59e0b',
            'urgent' => '#ef4444',
            'success' => '#10b981',
            default => '#6b7280',
        };
    }

    /**
     * Get type label in Swahili
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'referral' => 'Mwelekeo',
            'task' => 'Task',
            'bonus' => 'Bonus',
            'withdrawal' => 'Kutoa Pesa',
            'subscription' => 'Mpango',
            'warning' => 'Onyo',
            'urgent' => 'Dharura',
            'success' => 'Mafanikio',
            'system' => 'Mfumo',
            default => 'Taarifa',
        };
    }

    /**
     * Create a notification for a user
     */
    public static function notify(User $user, string $type, string $title, string $message, array $data = []): self
    {
        return static::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Create a notification for multiple users
     */
    public static function notifyMany(array $userIds, string $type, string $title, string $message, array $data = []): int
    {
        $notifications = [];
        $now = now();
        
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        return static::insert($notifications);
    }

    /**
     * Notify about task completion
     */
    public static function notifyTaskCompletion(User $user, $reward): self
    {
        return static::notify(
            $user,
            'task',
            '✅ Task Imekamilika!',
            "Umepata TZS " . number_format($reward, 0) . " kwa kukamilisha task.",
            ['reward' => $reward]
        );
    }

    /**
     * Notify about referral bonus
     */
    public static function notifyReferralBonus(User $user, $bonus, string $referredName): self
    {
        return static::notify(
            $user,
            'referral',
            '🎉 Bonus ya Mwelekeo!',
            "Umepata TZS " . number_format($bonus, 0) . " kutoka kwa {$referredName}.",
            ['bonus' => $bonus, 'referred_name' => $referredName]
        );
    }

    /**
     * Notify about withdrawal status
     */
    public static function notifyWithdrawal(User $user, string $status, $amount): self
    {
        $titles = [
            'approved' => '✅ Withdrawal Imekubaliwa',
            'paid' => '💰 Umelipwa!',
            'rejected' => '❌ Withdrawal Imekataliwa',
            'pending' => '⏳ Withdrawal Inasubiri',
        ];

        $messages = [
            'approved' => "Ombi lako la TZS " . number_format($amount, 0) . " limekubaliwa.",
            'paid' => "TZS " . number_format($amount, 0) . " zimetumwa kwenye akaunti yako.",
            'rejected' => "Ombi lako la TZS " . number_format($amount, 0) . " limekataliwa.",
            'pending' => "Ombi lako la TZS " . number_format($amount, 0) . " linasubiri.",
        ];

        return static::notify(
            $user,
            'withdrawal',
            $titles[$status] ?? 'Withdrawal',
            $messages[$status] ?? "Status: {$status}",
            ['status' => $status, 'amount' => $amount]
        );
    }

    /**
     * Notify about subscription
     */
    public static function notifySubscription(User $user, string $planName, string $action): self
    {
        $titles = [
            'activated' => '🎉 Mpango Umeanzishwa!',
            'expired' => '⏰ Mpango Umeisha',
            'expiring' => '⚠️ Mpango Unaisha Hivi Karibuni',
        ];

        $messages = [
            'activated' => "Umejiunga na mpango wa {$planName}. Furahia benefits zako!",
            'expired' => "Mpango wako wa {$planName} umeisha. Renew sasa!",
            'expiring' => "Mpango wako wa {$planName} unaisha hivi karibuni. Renew usikose!",
        ];

        return static::notify(
            $user,
            'subscription',
            $titles[$action] ?? 'Subscription',
            $messages[$action] ?? "Status: {$action}",
            ['plan_name' => $planName, 'action' => $action]
        );
    }
}
