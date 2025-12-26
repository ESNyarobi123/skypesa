<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'media_type',
        'video_path',
        'video_duration',
        'type',
        'icon',
        'is_active',
        'show_as_popup',
        'max_popup_views',
        'starts_at',
        'expires_at',
        'created_by',
    ];

    /**
     * Get the video URL for playback
     */
    public function getVideoUrlAttribute(): ?string
    {
        if ($this->video_path) {
            return asset('storage/' . $this->video_path);
        }
        return null;
    }

    /**
     * Check if this is a video announcement
     */
    public function isVideo(): bool
    {
        return $this->media_type === 'video' && $this->video_path;
    }

    protected $casts = [
        'is_active' => 'boolean',
        'show_as_popup' => 'boolean',
        'max_popup_views' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get announcement creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all reads for this announcement
     */
    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    /**
     * Check if announcement is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get active announcements
     */
    public static function getActiveAnnouncements()
    {
        return static::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if user should see popup for this announcement
     */
    public function shouldShowPopupFor(User $user): bool
    {
        if (!$this->show_as_popup || !$this->isCurrentlyActive()) {
            return false;
        }

        $read = $this->reads()->where('user_id', $user->id)->first();

        if (!$read) {
            return true; // Never seen
        }

        if ($read->popup_dismissed) {
            return false; // Already dismissed
        }

        return $read->view_count < $this->max_popup_views;
    }

    /**
     * Record that user viewed this announcement
     */
    public function recordView(User $user): AnnouncementRead
    {
        $read = AnnouncementRead::firstOrCreate(
            [
                'announcement_id' => $this->id,
                'user_id' => $user->id,
            ],
            [
                'first_seen_at' => now(),
            ]
        );

        $read->increment('view_count');
        $read->update(['last_seen_at' => now()]);

        // Auto-dismiss if max views reached
        if ($read->view_count >= $this->max_popup_views) {
            $read->update(['popup_dismissed' => true]);
        }

        return $read;
    }

    /**
     * Get type badge color
     */
    public function getTypeBadgeColor(): string
    {
        return match($this->type) {
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'urgent' => '#ef4444',
            default => '#3b82f6',
        };
    }

    /**
     * Get type icon
     */
    public function getTypeIcon(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        return match($this->type) {
            'success' => 'check-circle',
            'warning' => 'alert-triangle',
            'urgent' => 'alert-circle',
            default => 'info',
        };
    }
}
