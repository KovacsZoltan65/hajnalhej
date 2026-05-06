<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\RecipeStep;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductCreateFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Product::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product.category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where('is_active', true)),
            ],
            'product.name' => ['required', 'string', 'max:160'],
            'product.slug' => ['nullable', 'string', 'max:180'],
            'product.short_description' => ['nullable', 'string', 'max:255'],
            'product.description' => ['nullable', 'string', 'max:4000'],
            'product.price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'product.is_active' => ['required', 'boolean'],
            'product.is_featured' => ['required', 'boolean'],
            'product.stock_status' => ['required', Rule::in(Product::stockStatuses())],
            'product.image_path' => ['nullable', 'string', 'max:255'],
            'product.sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'ingredients' => ['array'],
            'ingredients.*.ingredient_id' => [
                'required',
                'integer',
                Rule::exists('ingredients', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
                'distinct',
            ],
            'ingredients.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999.999'],
            'ingredients.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'ingredients.*.notes' => ['nullable', 'string', 'max:1000'],
            'recipe_steps' => ['array'],
            'recipe_steps.*.title' => ['required', 'string', 'max:160'],
            'recipe_steps.*.step_type' => ['required', 'string', Rule::in(RecipeStep::stepTypes())],
            'recipe_steps.*.description' => ['nullable', 'string', 'max:4000'],
            'recipe_steps.*.work_instruction' => ['nullable', 'string', 'max:4000'],
            'recipe_steps.*.completion_criteria' => ['nullable', 'string', 'max:4000'],
            'recipe_steps.*.attention_points' => ['nullable', 'string', 'max:4000'],
            'recipe_steps.*.required_tools' => ['nullable', 'string', 'max:4000'],
            'recipe_steps.*.expected_result' => ['nullable', 'string', 'max:4000'],
            'recipe_steps.*.duration_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'recipe_steps.*.wait_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'recipe_steps.*.temperature_celsius' => ['nullable', 'numeric', 'between:-50,400'],
            'recipe_steps.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'recipe_steps.*.is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ((array) $this->input('recipe_steps', []) as $index => $step) {
                $duration = (int) ($step['duration_minutes'] ?? 0);
                $wait = (int) ($step['wait_minutes'] ?? 0);

                if ($duration > 0 || $wait > 0) {
                    continue;
                }

                $validator->errors()->add("recipe_steps.{$index}.duration_minutes", 'Legalabb az egyik idomezot add meg 0-nal nagyobb ertekkel.');
            }
        });
    }
}
