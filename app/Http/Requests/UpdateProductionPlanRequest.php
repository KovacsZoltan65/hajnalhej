<?php

namespace App\Http\Requests;

use App\Models\ProductionPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductionPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var ProductionPlan $productionPlan */
        $productionPlan = $this->route('productionPlan');

        return $this->user()?->can('update', $productionPlan) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_ready_at' => ['required_without:target_at', 'date'],
            'target_at' => ['required_without:target_ready_at', 'date'],
            'status' => ['required', 'string', Rule::in(ProductionPlan::statuses())],
            'is_locked' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.target_quantity' => ['required', 'numeric', 'gt:0', 'max:999999.999'],
            'items.*.unit_label' => ['nullable', 'string', 'max:24'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('target_ready_at')) {
            return;
        }

        $targetAt = $this->input('target_at');

        if ($targetAt === null || $targetAt === '') {
            return;
        }

        $this->merge([
            'target_ready_at' => $targetAt,
        ]);
    }
}
