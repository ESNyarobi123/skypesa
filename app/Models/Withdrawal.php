<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'amount',
        'fee',
        'net_amount',
        'payment_method',
        'payment_number',
        'payment_name',
        'payment_provider',
        'status',
        'rejection_reason',
        'zenopay_reference',
        'approved_at',
        'paid_at',
        'approved_by',
        'admin_notes',
        // Fraud prevention fields
        'processable_at',
        'delay_hours',
        'risk_score',
        'risk_factors',
        'is_frozen',
        'freeze_reason',
        'frozen_by',
        'frozen_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'processable_at' => 'datetime',
        'frozen_at' => 'datetime',
        'risk_factors' => 'array',
        'is_frozen' => 'boolean',
    ];

    protected $attributes = [
        'delay_hours' => 24,
        'risk_score' => 0,
        'is_frozen' => false,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($withdrawal) {
            if (empty($withdrawal->reference)) {
                $withdrawal->reference = 'WD' . strtoupper(Str::random(10));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(User $admin, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $admin->id,
            'admin_notes' => $notes,
        ]);
    }

    public function markAsPaid(?string $zenoPayReference = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'zenopay_reference' => $zenoPayReference,
        ]);

        // Release pending withdrawal from wallet
        $this->user->wallet->decrement('pending_withdrawal', $this->amount);
    }

    public function reject(string $reason, User $admin): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $admin->id,
        ]);

        // Restore balance and release pending
        $wallet = $this->user->wallet;
        $wallet->credit($this->amount, 'refund', $this, 'Withdrawal rejected: ' . $reason);
        $wallet->decrement('pending_withdrawal', $this->amount);
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'approved' => 'green',
            'paid' => 'green',
            'rejected' => 'red',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Inasubiri',
            'processing' => 'Inachakatwa',
            'approved' => 'Imekubaliwa',
            'paid' => 'Imelipwa',
            'rejected' => 'Imekataliwa',
            'cancelled' => 'Imefutwa',
            default => ucfirst($this->status),
        };
    }

    // ==========================================
    // Fraud Prevention Methods
    // ==========================================

    /**
     * Freeze the withdrawal pending review
     */
    public function freeze(string $reason, User $admin): void
    {
        $this->update([
            'is_frozen' => true,
            'freeze_reason' => $reason,
            'frozen_by' => $admin->id,
            'frozen_at' => now(),
        ]);
    }

    /**
     * Unfreeze the withdrawal
     */
    public function unfreeze(): void
    {
        $this->update([
            'is_frozen' => false,
            'freeze_reason' => null,
            'frozen_by' => null,
            'frozen_at' => null,
        ]);
    }

    /**
     * Check if withdrawal is frozen
     */
    public function isFrozen(): bool
    {
        return $this->is_frozen;
    }

    /**
     * Set risk assessment data
     */
    public function setRiskAssessment(int $score, array $factors, int $delayHours): void
    {
        $this->update([
            'risk_score' => $score,
            'risk_factors' => $factors,
            'delay_hours' => $delayHours,
            'processable_at' => now()->addHours($delayHours),
        ]);
    }

    /**
     * Check if withdrawal can be processed (delay has passed)
     */
    public function isProcessable(): bool
    {
        if ($this->is_frozen) {
            return false;
        }

        if ($this->processable_at && now()->lt($this->processable_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get remaining delay time in human readable format
     */
    public function getDelayRemaining(): ?string
    {
        if (!$this->processable_at || now()->gte($this->processable_at)) {
            return null;
        }

        return now()->diffForHumans($this->processable_at, true);
    }

    /**
     * Get risk level label
     */
    public function getRiskLevel(): string
    {
        if ($this->risk_score >= 80) {
            return 'critical';
        }
        if ($this->risk_score >= 60) {
            return 'high';
        }
        if ($this->risk_score >= 30) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Get risk level color for UI
     */
    public function getRiskColor(): string
    {
        return match($this->getRiskLevel()) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
        };
    }

    /**
     * Scope for processable withdrawals
     */
    public function scopeProcessable($query)
    {
        return $query->where('is_frozen', false)
            ->where(function ($q) {
                $q->whereNull('processable_at')
                    ->orWhere('processable_at', '<=', now());
            });
    }

    /**
     * Scope for frozen withdrawals
     */
    public function scopeFrozen($query)
    {
        return $query->where('is_frozen', true);
    }

    /**
     * Scope for high risk withdrawals
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_score', '>=', 60);
    }

    /**
     * Get relation for who froze it
     */
    public function frozenBy()
    {
        return $this->belongsTo(User::class, 'frozen_by');
    }
}
