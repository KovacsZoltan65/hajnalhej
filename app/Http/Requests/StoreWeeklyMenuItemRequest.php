<?php

namespace App\Http\Requests;

use App\Models\WeeklyMenu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWeeklyMenuItemRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'override_price' => $this->input('override_price', $this->input('price_override')),
            'price_override' => $this->input('price_override', $this->input('override_price')),
            'is_active' => $this->input('is_active', $this->input('is_available', true)),
            'is_available' => $this->input('is_available', $this->input('is_active', true)),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var WeeklyMenu $weeklyMenu */
        $weeklyMenu = $this->route('weeklyMenu');

        return $this->user()?->can('update', $weeklyMenu) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var WeeklyMenu $weeklyMenu */
        $weeklyMenu = $this->route('weeklyMenu');

        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
                Rule::unique('weekly_menu_items', 'product_id')->where(
                    fn ($query) => $query->where('weekly_menu_id', $weeklyMenu->id)
                ),
            ],
            'override_name' => ['nullable', 'string', 'max:160'],
            'override_short_description' => ['nullable', 'string', 'max:255'],
            'override_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'price_override' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['required', 'boolean'],
            'is_available' => ['required', 'boolean'],
            'badge_text' => ['nullable', 'string', 'max:80'],
            'stock_note' => ['nullable', 'string', 'max:160'],
        ];
    }
}
