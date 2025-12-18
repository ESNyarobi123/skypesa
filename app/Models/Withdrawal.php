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
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
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
}
