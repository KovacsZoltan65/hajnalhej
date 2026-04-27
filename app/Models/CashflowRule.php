<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashflowRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rule_type',
        'threshold_amount',
        'warning_percent',
        'lookahead_days',
        'action',
        'active',
        'priority',
        'conditions',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'threshold_amount' => 'decimal:2',
            'warning_percent' => 'decimal:4',
            'lookahead_days' => 'integer',
            'active' => 'boolean',
            'priority' => 'integer',
            'conditions' => 'array',
        ];
    }
}
