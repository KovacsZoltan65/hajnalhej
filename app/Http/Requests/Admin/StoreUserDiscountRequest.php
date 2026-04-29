<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Models\UserDiscount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageDiscounts', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(UserDiscount::types())],
            'value' => [
                'required',
                'numeric',
                Rule::when($this->input('type') === UserDiscount::TYPE_PERCENT, ['min:0', 'max:100'], ['gt:0']),
            ],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'active' => ['boolean'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
