<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $supplier_id
 * @property int|null $ingredient_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $planned_on
 * @property \Illuminate\Support\Carbon|null $completed_on
 * @property numeric|null $current_unit_cost
 * @property numeric|null $target_unit_cost
 * @property numeric|null $expected_savings
 * @property numeric|null $achieved_savings
 * @property string|null $talking_points
 * @property string|null $outcome_notes
 * @property array<array-key, mixed>|null $evidence_snapshot
 * @property int|null $owner_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Ingredient|null $ingredient
 * @property-read \App\Models\User|null $owner
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereAchievedSavings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereCompletedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereCurrentUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereEvidenceSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereExpectedSavings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereOutcomeNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation wherePlannedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereTalkingPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereTargetUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierNegotiation withoutTrashed()
 * @mixin \Eloquent
 */
class SupplierNegotiation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'ingredient_id',
        'status',
        'planned_on',
        'completed_on',
        'current_unit_cost',
        'target_unit_cost',
        'expected_savings',
        'achieved_savings',
        'talking_points',
        'outcome_notes',
        'evidence_snapshot',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'planned_on' => 'date',
            'completed_on' => 'date',
            'current_unit_cost' => 'decimal:2',
            'target_unit_cost' => 'decimal:2',
            'expected_savings' => 'decimal:2',
            'achieved_savings' => 'decimal:2',
            'evidence_snapshot' => 'array',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
