<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pool Link Model
 * 
 * Individual links within a LinkPool.
 * Tracks clicks and performance per link.
 */
class PoolLink extends Model
{
    protected $fillable = [
        'link_pool_id',
        'name',
        'url',
        'provider',
        'total_clicks',
        'clicks_today',
        'last_click_at',
        'weight',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'total_clicks' => 'integer',
        'clicks_today' => 'integer',
        'last_click_at' => 'datetime',
        'weight' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the pool this link belongs to
     */
    public function pool(): BelongsTo
    {
        return $this->belongsTo(LinkPool::class, 'link_pool_id');
    }

    /**
     * Record a click on this link
     */
    public function recordClick(): void
    {
        $this->increment('total_clicks');
        $this->increment('clicks_today');
        $this->update(['last_click_at' => now()]);
    }

    /**
     * Reset daily clicks (called by scheduler)
     */
    public static function resetDailyClicks(): void
    {
        static::query()->update(['clicks_today' => 0]);
    }

    /**
     * Get provider icon
     */
    public function getProviderIconAttribute(): string
    {
        return match(strtolower($this->provider)) {
            'adsterra' => '🌟',
            'monetag' => '💰',
            'propellerads' => '🚀',
            'hilltopads' => '⛰️',
            default => '🔗',
        };
    }

    /**
     * Get click rate (percentage of pool clicks)
     */
    public function getClickRateAttribute(): float
    {
        $poolTotal = $this->pool->total_clicks;
        if ($poolTotal === 0) return 0;
        
        return round(($this->total_clicks / $poolTotal) * 100, 1);
    }
}
