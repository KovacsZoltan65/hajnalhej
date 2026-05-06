<?php

namespace App\Http\Requests\Admin;

use App\Models\WeeklyMenu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InlineUpdateWeeklyMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WeeklyMenu $weeklyMenu */
        $weeklyMenu = $this->route('weeklyMenu');

        return $this->user()?->can('update', $weeklyMenu) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'field' => ['required', Rule::in(['status'])],
            'value' => ['required', Rule::in(WeeklyMenu::statuses())],
        ];
    }
}
