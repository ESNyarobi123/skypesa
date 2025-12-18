<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'total_earned',
        'total_withdrawn',
        'pending_withdrawal',
        'is_locked',
        'lock_reason',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'pending_withdrawal' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function credit(float $amount, string $category, $transactionable = null, ?string $description = null, ?array $metadata = null): Transaction
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->total_earned += $amount;
        $this->save();

        return $this->createTransaction('credit', $category, $amount, $balanceBefore, $transactionable, $description, $metadata);
    }

    public function debit(float $amount, string $category, $transactionable = null, ?string $description = null, ?array $metadata = null): Transaction
    {
        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        
        if ($category === 'withdrawal') {
            $this->total_withdrawn += $amount;
        }
        
        $this->save();

        return $this->createTransaction('debit', $category, $amount, $balanceBefore, $transactionable, $description, $metadata);
    }

    protected function createTransaction(string $type, string $category, float $amount, float $balanceBefore, $transactionable, ?string $description, ?array $metadata): Transaction
    {
        return Transaction::create([
            'user_id' => $this->user_id,
            'wallet_id' => $this->id,
            'reference' => 'TXN' . strtoupper(Str::random(12)),
            'type' => $type,
            'category' => $category,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'transactionable_type' => $transactionable ? get_class($transactionable) : null,
            'transactionable_id' => $transactionable?->id,
            'metadata' => $metadata,
        ]);
    }

    public function getAvailableBalance(): float
    {
        return max(0, $this->balance - $this->pending_withdrawal);
    }

    public function canWithdraw(float $amount): bool
    {
        if ($this->is_locked) {
            return false;
        }

        return $this->getAvailableBalance() >= $amount;
    }

    public function lock(string $reason): void
    {
        $this->update([
            'is_locked' => true,
            'lock_reason' => $reason,
        ]);
    }

    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'lock_reason' => null,
        ]);
    }
}
