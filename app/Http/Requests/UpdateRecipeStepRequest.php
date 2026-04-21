<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\RecipeStep;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRecipeStepRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Product $product */
        $product = $this->route('product');

        return $this->user()?->can('update', $product) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'step_type' => ['required', 'string', Rule::in(RecipeStep::stepTypes())],
            'description' => ['nullable', 'string', 'max:4000'],
            'work_instruction' => ['nullable', 'string', 'max:4000'],
            'completion_criteria' => ['nullable', 'string', 'max:4000'],
            'attention_points' => ['nullable', 'string', 'max:4000'],
            'required_tools' => ['nullable', 'string', 'max:4000'],
            'expected_result' => ['nullable', 'string', 'max:4000'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'wait_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'temperature_celsius' => ['nullable', 'numeric', 'between:-50,400'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $duration = (int) ($this->input('duration_minutes') ?? 0);
            $wait = (int) ($this->input('wait_minutes') ?? 0);

            if ($duration > 0 || $wait > 0) {
                return;
            }

            $message = 'Legalabb az egyik idomezot add meg 0-nal nagyobb ertekkel (aktiv ido vagy varakozasi ido).';
            $validator->errors()->add('duration_minutes', $message);
            $validator->errors()->add('wait_minutes', $message);
        });
    }
}
