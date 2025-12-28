<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'ticket_number',
        'subject',
        'category',
        'priority',
        'initial_message',
        'status',
        'assigned_to',
        'last_message_at',
        'resolved_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'open',
        'priority' => 'medium',
        'category' => 'general',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class)->orderBy('created_at', 'asc');
    }

    public function lastMessage()
    {
        return $this->hasOne(SupportMessage::class)->latest();
    }

    public function unreadMessagesCount()
    {
        return $this->messages()->where('is_read', false)->where('is_admin', true)->count();
    }

    public function unreadAdminMessagesCount()
    {
        return $this->messages()->where('is_read', false)->where('is_admin', false)->count();
    }

    // Status check methods
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    // Status action methods
    public function markAsInProgress(?User $admin = null): void
    {
        $this->update([
            'status' => 'in_progress',
            'assigned_to' => $admin?->id,
        ]);
    }

    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'resolved_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'resolved_at' => null,
        ]);
    }

    // Helper methods
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'open' => 'Wazi',
            'in_progress' => 'Inashughulikiwa',
            'resolved' => 'Imetatuliwa',
            'closed' => 'Imefungwa',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'open' => 'yellow',
            'in_progress' => 'blue',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function getPriorityLabel(): string
    {
        return match($this->priority) {
            'low' => 'Chini',
            'medium' => 'Wastani',
            'high' => 'Juu',
            default => ucfirst($this->priority),
        };
    }

    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'red',
            default => 'gray',
        };
    }

    public function getCategoryLabel(): string
    {
        return match($this->category) {
            'general' => 'Jumla',
            'task' => 'Task',
            'withdrawal' => 'Kutoa Pesa',
            'subscription' => 'Subscription',
            'account' => 'Akaunti',
            'bug' => 'Hitilafu',
            'other' => 'Nyingine',
            default => ucfirst($this->category),
        };
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }
}

