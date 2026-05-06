<?php

namespace App\Http\Requests\Admin;

use App\Models\Ingredient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InlineUpdateIngredientRequest extends FormRequest
{
    /**
     * @return array<int, string>
     */
    public static function allowedFields(): array
    {
        return ['current_stock', 'minimum_stock', 'unit'];
    }

    public function authorize(): bool
    {
        /** @var Ingredient $ingredient */
        $ingredient = $this->route('ingredient');

        return $this->user()?->can('update', $ingredient) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'field' => ['required', Rule::in(self::allowedFields())],
            'value' => ['required'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $field = (string) $this->input('field');
            $value = $this->input('value');

            $rules = match ($field) {
                'current_stock', 'minimum_stock' => ['numeric', 'min:0', 'max:999999999.999'],
                'unit' => [Rule::in(Ingredient::allowedUnits())],
                default => [],
            };

            $inlineValidator = validator(['value' => $value], ['value' => $rules]);

            if ($inlineValidator->fails()) {
                foreach ($inlineValidator->errors()->get('value') as $message) {
                    $validator->errors()->add('value', $message);
                }
            }
        });
    }
}
