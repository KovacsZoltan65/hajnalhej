<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDiscount extends Model
{
    use HasFactory;

    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'user_id',
        'type',
        'value',
        'starts_at',
        'expires_at',
        'active',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCurrentlyValid(Builder $query): Builder
    {
        return $query
            ->where('active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public static function types(): array
    {
        return [self::TYPE_PERCENT, self::TYPE_FIXED];
    }
}
