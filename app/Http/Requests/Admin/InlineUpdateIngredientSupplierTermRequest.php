<?php

namespace App\Http\Requests\Admin;

use App\Models\IngredientSupplierTerm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InlineUpdateIngredientSupplierTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var IngredientSupplierTerm $ingredientSupplierTerm */
        $ingredientSupplierTerm = $this->route('ingredientSupplierTerm');

        return $this->user()?->can('update', $ingredientSupplierTerm) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'field' => ['required', Rule::in(['preferred', 'active', 'lead_time_days', 'unit_cost_override'])],
            'value' => ['nullable'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $field = (string) $this->input('field');
            $rules = match ($field) {
                'preferred', 'active' => ['boolean'],
                'lead_time_days' => ['nullable', 'integer', 'min:0', 'max:365'],
                'unit_cost_override' => ['nullable', 'numeric', 'min:0', 'max:99999999.9999'],
                default => [],
            };
            $inlineValidator = validator(['value' => $this->input('value')], ['value' => $rules]);

            foreach ($inlineValidator->errors()->get('value') as $message) {
                $validator->errors()->add('value', $message);
            }
        });
    }
}
