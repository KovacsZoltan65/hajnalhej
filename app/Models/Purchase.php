<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_CANCELLED = 'cancelled';

    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'reference_number',
        'purchase_date',
        'status',
        'subtotal',
        'total',
        'notes',
        'created_by',
        'posted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_POSTED,
            self::STATUS_CANCELLED,
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

