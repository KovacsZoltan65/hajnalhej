<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $purchase_receipt_id
 * @property int|null $purchase_item_id
 * @property int $ingredient_id
 * @property numeric $ordered_quantity
 * @property numeric $received_quantity
 * @property numeric $rejected_quantity
 * @property string $unit
 * @property numeric $unit_cost
 * @property numeric $line_total
 * @property string $quality_status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\PurchaseItem|null $purchaseItem
 * @property-read \App\Models\PurchaseReceipt $receipt
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereOrderedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem wherePurchaseItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem wherePurchaseReceiptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereQualityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereReceivedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereRejectedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseReceiptItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PurchaseReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_receipt_id',
        'purchase_item_id',
        'ingredient_id',
        'ordered_quantity',
        'received_quantity',
        'rejected_quantity',
        'unit',
        'unit_cost',
        'line_total',
        'quality_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ordered_quantity' => 'decimal:3',
            'received_quantity' => 'decimal:3',
            'rejected_quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
