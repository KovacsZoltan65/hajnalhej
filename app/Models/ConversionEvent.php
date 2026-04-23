<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionEvent extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'event_key',
        'funnel',
        'step',
        'cta_id',
        'hero_variant',
        'source',
        'user_id',
        'session_id',
        'path',
        'url',
        'referrer',
        'ip_hash',
        'user_agent',
        'metadata',
        'occurred_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

