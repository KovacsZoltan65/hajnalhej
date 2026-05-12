<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\LocaleSettingsService;
use Illuminate\Foundation\Http\FormRequest;

final class SwitchLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'locale' => app(LocaleSettingsService::class)->validationRules(),
        ];
    }

    public function locale(): string
    {
        return (string) $this->validated('locale');
    }
}
