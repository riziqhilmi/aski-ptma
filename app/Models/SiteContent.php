<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteContent extends Model
{
    public const KEY_PROFILE = 'profile';
    public const KEY_VISION_MISSION = 'vision_mission';
    public const KEY_CONTACT = 'contact';

    public const KEYS = [
        self::KEY_PROFILE,
        self::KEY_VISION_MISSION,
        self::KEY_CONTACT,
    ];

    protected $fillable = [
        'key',
        'title',
        'body',
        'metadata',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
