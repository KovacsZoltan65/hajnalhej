<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductionPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductionPlanCreateFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProductionPlan::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'target_ready_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
            ],
            'items.*.target_quantity' => ['required', 'numeric', 'gt:0', 'max:999999.999'],
            'items.*.unit_label' => ['nullable', 'string', 'max:24'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }
}
