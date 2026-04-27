<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Spatie\Activitylog\LogOptions;
//use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role withoutPermission($permissions)
 * @mixin \Eloquent
 */
class Role extends SpatieRole
{
    use HasFactory;

    /** @var array<int,string> */
    protected $fillable = ['name', 'guard_name'];

    protected $guarded = [];

    /** Rendezhető mezők (helperhez / validáláshoz) */
    /** @var array<int,string> */
    protected static array $sortable = ['name', 'guard_name'];

    /** (Opcionálisan) típus-casting – a Carbon megtartásához hasznos */
    /** @var array<string,string> */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    
    protected static string $logName = 'roles';
    /*
    protected static bool $logOnlyDirty = true;

    protected static array $recordEvents = ['created', 'updated', 'deleted'];

    public function getLogNameToUse(string $eventName = ''): string
    {
        return static::$logName ?? 'default';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }
    */
    public static function getTag(): string
    {
        return self::$logName;
    }
    

    /** @return array<int,string> */
    public static function getSortable(): array
    {
        return self::$sortable;
    }

    /**
     * @return array<int, array{id:int, name:string}>
     */
    public static function getToSelect(): array
    {
        return static::query()
            ->select(['id', 'name'])
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn ($r) => ['id' => (int) $r->id, 'name' => (string) $r->name])
            ->all();
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('d-m-Y H:i');
    }
}
