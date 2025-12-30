<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'version_code',
        'version_name',
        'apk_path',
        'description',
        'features',
        'screenshots',
        'is_active',
        'force_update',
    ];

    protected $casts = [
        'features' => 'array',
        'screenshots' => 'array',
        'is_active' => 'boolean',
        'force_update' => 'boolean',
    ];
}
