<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'email',
        'phone',
        'address',
        'active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(BranchTransfer::class, 'from_branch_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(BranchTransfer::class, 'to_branch_id');
    }
}
