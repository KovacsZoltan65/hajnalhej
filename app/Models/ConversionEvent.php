<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $event_key
 * @property string|null $funnel
 * @property string|null $step
 * @property string|null $cta_id
 * @property string|null $hero_variant
 * @property string $source
 * @property int|null $user_id
 * @property string|null $session_id
 * @property string|null $path
 * @property string|null $url
 * @property string|null $referrer
 * @property string|null $ip_hash
 * @property string|null $user_agent
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon $occurred_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereCtaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereEventKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereFunnel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereHeroVariant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereIpHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereOccurredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereReferrer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversionEvent whereUserId($value)
 * @mixin \Eloquent
 */
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

