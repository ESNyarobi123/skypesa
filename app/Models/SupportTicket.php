<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
}
