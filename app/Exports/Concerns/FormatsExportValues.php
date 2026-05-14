<?php

declare(strict_types=1);

namespace App\Exports\Concerns;

use Illuminate\Support\Carbon;
use JsonSerializable;

trait FormatsExportValues
{
    protected function dateTime(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        return $value ? (string) $value : '';
    }

    protected function money(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    protected function decimal(mixed $value, int $precision = 3): string
    {
        return number_format((float) $value, $precision, '.', '');
    }

    protected function humanStatus(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return str((string) $value)->replace(['_', '-'], ' ')->headline()->toString();
    }

    /**
     * @param  array<string, mixed>|JsonSerializable|string|null  $properties
     */
    protected function sanitizedJson(array|JsonSerializable|string|null $properties): string
    {
        if ($properties === null || $properties === '') {
            return '';
        }

        $payload = $properties instanceof JsonSerializable ? $properties->jsonSerialize() : $properties;
        $payload = is_string($payload) ? json_decode($payload, true) : $payload;

        if (! is_array($payload)) {
            return '';
        }

        return json_encode($this->sanitize($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    /**
     * @param  array<string|int, mixed>  $payload
     * @return array<string|int, mixed>
     */
    private function sanitize(array $payload): array
    {
        $sensitive = ['password', 'token', 'secret', 'api_key', 'apikey', 'authorization', 'remember_token'];

        foreach ($payload as $key => $value) {
            $normalized = str((string) $key)->lower()->replace(['-', ' '], '_')->toString();

            if (in_array($normalized, $sensitive, true) || str_contains($normalized, 'password') || str_contains($normalized, 'token') || str_contains($normalized, 'secret')) {
                $payload[$key] = '[masked]';

                continue;
            }

            if (is_array($value)) {
                $payload[$key] = $this->sanitize($value);
            }
        }

        return $payload;
    }
}
