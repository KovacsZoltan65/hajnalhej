<?php

namespace App\Http\Requests;

use App\Models\WeeklyMenu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWeeklyMenuRequest extends FormRequest
{
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
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash', Rule::unique('weekly_menus', 'slug')->ignore($weeklyMenu->id)],
            'week_start' => ['required', 'date'],
            'week_end' => ['required', 'date', 'after_or_equal:week_start'],
            'status' => ['nullable', Rule::in(WeeklyMenu::statuses())],
            'public_note' => ['nullable', 'string', 'max:3000'],
            'internal_note' => ['nullable', 'string', 'max:3000'],
            'is_featured' => ['required', 'boolean'],
        ];
    }
}
