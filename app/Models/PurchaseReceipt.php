<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $purchase_id
 * @property string $receipt_number
 * @property Carbon $received_date
 * @property string $status
 * @property numeric $total_received_value
 * @property string|null $notes
 * @property int|null $received_by
 * @property Carbon|null $posted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PurchaseReceiptItem> $items
 * @property-read int|null $items_count
 * @property-read Purchase $purchase
 * @property-read User|null $receiver
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt wherePostedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt whereReceiptNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt whereReceivedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt whereReceivedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt whereTotalReceivedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceipt whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PurchaseReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'receipt_number',
        'received_date',
        'status',
        'total_received_value',
        'notes',
        'received_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'total_received_value' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
