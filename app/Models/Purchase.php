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
        'receipt_status',
        'subtotal',
        'total',
        'received_total',
        'notes',
        'created_by',
        'expected_delivery_date',
        'received_date',
        'ordered_at',
        'posted_at',
        'cancelled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'expected_delivery_date' => 'date',
            'received_date' => 'date',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'received_total' => 'decimal:2',
            'ordered_at' => 'datetime',
            'posted_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(PurchaseRecommendation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
