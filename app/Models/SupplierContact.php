<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $supplier_id
 * @property string $name
 * @property string|null $role
 * @property string|null $email
 * @property string|null $phone
 * @property bool $is_primary
 * @property bool $active
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierContact withoutTrashed()
 * @mixin \Eloquent
 */
class SupplierContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'name',
        'role',
        'email',
        'phone',
        'is_primary',
        'active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
