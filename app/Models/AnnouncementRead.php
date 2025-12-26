<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementRead extends Model
{
    protected $fillable = [
        'announcement_id',
        'user_id',
        'view_count',
        'popup_dismissed',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'view_count' => 'integer',
        'popup_dismissed' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the announcement
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
