<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

