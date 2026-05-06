<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InlineUpdateProductRequest extends FormRequest
{
    /**
     * @return array<int, string>
     */
    public static function allowedFields(): array
    {
        return ['price', 'is_active', 'category_id', 'stock_status'];
    }

    public function authorize(): bool
    {
        /** @var Product $product */
        $product = $this->route('product');

        return $this->user()?->can('update', $product) ?? false;
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
                'price' => ['numeric', 'min:0', 'max:99999999.99'],
                'is_active' => ['boolean'],
                'category_id' => [
                    'integer',
                    Rule::exists('categories', 'id')->where(fn ($query) => $query
                        ->whereNull('deleted_at')
                        ->where('is_active', true)),
                ],
                'stock_status' => [Rule::in(Product::stockStatuses())],
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
