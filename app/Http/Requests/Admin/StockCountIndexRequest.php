<?php

namespace App\Http\Requests\Admin;

use App\Models\StockCount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockCountIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(StockCount::statuses())],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ];
    }
}

