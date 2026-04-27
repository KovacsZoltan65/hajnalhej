<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $purchase_id
 * @property string $receipt_number
 * @property \Illuminate\Support\Carbon $received_date
 * @property string $status
 * @property numeric $total_received_value
 * @property string|null $notes
 * @property int|null $received_by
 * @property \Illuminate\Support\Carbon|null $posted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseReceiptItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Purchase $purchase
 * @property-read \App\Models\User|null $receiver
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
