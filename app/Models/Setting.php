<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("setting.{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $description = null): static
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget("setting.{$key}");
        Cache::forget('settings.all');

        return $setting;
    }

    /**
     * Get all settings as array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            $settings = [];
            foreach (static::all() as $setting) {
                $settings[$setting->key] = static::castValue($setting->value, $setting->type);
            }
            return $settings;
        });
    }

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group): array
    {
        $settings = [];
        foreach (static::where('group', $group)->get() as $setting) {
            $settings[$setting->key] = static::castValue($setting->value, $setting->type);
        }
        return $settings;
    }

    /**
     * Cast value based on type
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("setting.{$setting->key}");
        }
        Cache::forget('settings.all');
    }
}
