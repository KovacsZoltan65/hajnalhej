<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcurementIntelligenceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'days' => ['nullable', 'integer', Rule::in([7, 30, 90, 180])],
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'urgency' => ['nullable', 'string', Rule::in(['critical', 'high', 'medium', 'low'])],
            'alert_type' => ['nullable', 'string', Rule::in([
                'low_stock',
                'stockout_risk',
                'price_increase',
                'stale_purchase_data',
                'missing_estimated_cost',
                'missing_minimum_stock',
                'bom_no_stock',
            ])],
        ];
    }
}
