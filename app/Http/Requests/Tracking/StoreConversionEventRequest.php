<?php

namespace App\Http\Requests\Tracking;

use App\Services\HeroExperimentService;
use App\Support\ConversionEventRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversionEventRequest extends FormRequest
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
            'event_key' => ['required', 'string', Rule::in(ConversionEventRegistry::eventKeys())],
            'funnel' => ['nullable', 'string', Rule::in(ConversionEventRegistry::funnels())],
            'step' => ['nullable', 'string', 'max:80'],
            'cta_id' => ['nullable', 'string', 'max:120'],
            'hero_variant' => ['nullable', 'string', Rule::in(app(HeroExperimentService::class)->variants())],
            'metadata' => ['nullable', 'array'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}

