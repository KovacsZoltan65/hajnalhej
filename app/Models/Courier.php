<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string $status
 * @property string|null $vehicle_type
 * @property bool $active
 * @property string|null $notes
 * @property array<array-key, mixed>|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Order> $orders
 *
 * @mixin \Eloquent
 */
class Courier extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'status',
        'vehicle_type',
        'active',
        'notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'active' => 'boolean',
            'meta' => 'array',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
