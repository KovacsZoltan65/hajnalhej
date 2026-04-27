<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $count_date
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockCountItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereCountDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StockCount extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CLOSED = 'closed';

    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'count_date',
        'status',
        'notes',
        'created_by',
        'closed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'count_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_CLOSED,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

