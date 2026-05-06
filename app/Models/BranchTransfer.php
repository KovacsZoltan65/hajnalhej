<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $from_branch_id
 * @property int $to_branch_id
 * @property int $ingredient_id
 * @property string $transfer_number
 * @property string $status
 * @property numeric $quantity
 * @property string $unit
 * @property Carbon $requested_date
 * @property Carbon|null $transferred_date
 * @property string|null $notes
 * @property int|null $requested_by
 * @property int|null $completed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $completer
 * @property-read Branch|null $fromBranch
 * @property-read Ingredient|null $ingredient
 * @property-read User|null $requester
 * @property-read Branch|null $toBranch
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereCompletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereFromBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereRequestedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereRequestedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereToBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereTransferNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereTransferredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchTransfer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class BranchTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'ingredient_id',
        'transfer_number',
        'status',
        'quantity',
        'unit',
        'requested_date',
        'transferred_date',
        'notes',
        'requested_by',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'requested_date' => 'date',
            'transferred_date' => 'date',
        ];
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
