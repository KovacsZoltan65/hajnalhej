<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(ExportType::class)],
            'format' => ['required', new Enum(ExportFormat::class)],
            'filters' => ['nullable', 'array'],
            'filters.status' => ['nullable', 'string', 'max:64'],
            'filters.fulfillment_method' => ['nullable', 'string', 'max:64'],
            'filters.date_from' => ['nullable', 'date'],
            'filters.date_to' => ['nullable', 'date', 'after_or_equal:filters.date_from'],
            'filters.search' => ['nullable', 'string', 'max:160'],
            'filters.category_id' => ['nullable', 'integer'],
            'filters.is_active' => ['nullable'],
            'filters.low_stock' => ['nullable'],
            'filters.ingredient_id' => ['nullable', 'integer'],
            'filters.movement_type' => ['nullable', 'string', 'max:64'],
            'filters.direction' => ['nullable', 'string', 'max:16'],
            'filters.log_name' => ['nullable', 'string', 'max:64'],
            'filters.event' => ['nullable', 'string', 'max:64'],
            'filters.causer_id' => ['nullable', 'integer'],
            'filters.subject_type' => ['nullable', 'string', 'max:160'],
        ];
    }
}
