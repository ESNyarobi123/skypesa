<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'reference',
        'type',
        'category',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'transactionable_type',
        'transactionable_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }

    public function getFormattedAmount(): string
    {
        $prefix = $this->isCredit() ? '+' : '-';
        return $prefix . ' TZS ' . number_format($this->amount, 0);
    }

    public function getCategoryLabel(): string
    {
        return match($this->category) {
            'task_reward' => 'Task Reward',
            'withdrawal' => 'Withdrawal',
            'withdrawal_fee' => 'Withdrawal Fee',
            'deposit' => 'Deposit',
            'subscription' => 'Subscription',
            'bonus' => 'Bonus',
            'refund' => 'Refund',
            'adjustment' => 'Adjustment',
            default => ucfirst($this->category),
        };
    }
}
