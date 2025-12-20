<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'survey_id',
        'transaction_id',
        'survey_type',
        'loi',
        'cpx_payout',
        'cpx_payout_tzs',
        'user_reward',
        'vip_bonus',
        'profit_margin',
        'status',
        'ip_address',
        'user_agent',
        'cpx_data',
        'completed_at',
        'credited_at',
    ];

    protected $casts = [
        'cpx_payout' => 'decimal:2',
        'cpx_payout_tzs' => 'decimal:2',
        'user_reward' => 'decimal:2',
        'vip_bonus' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'cpx_data' => 'array',
        'completed_at' => 'datetime',
        'credited_at' => 'datetime',
    ];

    // Survey type constants with rewards
    const SURVEY_TYPES = [
        'short' => [
            'min_loi' => 1,
            'max_loi' => 7,
            'reward' => 200,
            'label' => 'Short Survey',
            'vip_only' => false,
        ],
        'medium' => [
            'min_loi' => 8,
            'max_loi' => 14,
            'reward' => 300,
            'label' => 'Medium Survey',
            'vip_only' => false,
        ],
        'long' => [
            'min_loi' => 15,
            'max_loi' => 999,
            'reward' => 500,
            'label' => 'Long Survey',
            'vip_only' => true,
        ],
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCredited($query)
    {
        return $query->where('status', 'credited');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Helper Methods
    public static function getSurveyType(int $loi): string
    {
        if ($loi <= 7) {
            return 'short';
        } elseif ($loi <= 14) {
            return 'medium';
        }
        return 'long';
    }

    public static function getRewardForLoi(int $loi, User $user): float
    {
        $type = self::getSurveyType($loi);
        $config = self::SURVEY_TYPES[$type];

        // Check if VIP only
        if ($config['vip_only']) {
            $plan = $user->getCurrentPlan();
            if (!$plan || !in_array($plan->name, ['diamond', 'vip', 'premium'])) {
                // Non-VIP users get medium reward for long surveys
                return self::SURVEY_TYPES['medium']['reward'];
            }
        }

        return $config['reward'];
    }

    public function getTypeLabel(): string
    {
        if ($this->survey_type === 'screenout') {
            return 'Screenout';
        }
        return self::SURVEY_TYPES[$this->survey_type]['label'] ?? 'Survey';
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Inasubiri',
            'completed' => 'Imekamilika',
            'credited' => 'Imelipwa',
            'rejected' => 'Imekataliwa',
            'reversed' => 'Imerudishwa',
            'screenout' => 'Haijakamilika',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'completed' => 'info',
            'credited' => 'success',
            'rejected' => 'error',
            'reversed' => 'error',
            'screenout' => 'secondary',
            default => 'secondary',
        };
    }

    // Scope for screenouts
    public function scopeScreenout($query)
    {
        return $query->where('status', 'screenout');
    }

    // Get formatted profit margin
    public function getProfitFormattedAttribute(): string
    {
        return 'TZS ' . number_format($this->profit_margin, 0);
    }

    // Get total reward (base + bonus)
    public function getTotalRewardAttribute(): float
    {
        return $this->user_reward;
    }

    // Get base reward (without bonus)
    public function getBaseRewardAttribute(): float
    {
        return $this->user_reward - ($this->vip_bonus ?? 0);
    }
}

