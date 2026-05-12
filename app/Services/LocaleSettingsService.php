<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;

final class LocaleSettingsService
{
    public function validationRule(): string
    {
        return 'required|string|in:'.implode(',', $this->supportedLocales());
    }

    /**
     * @return list<string>
     */
    public function validationRules(): array
    {
        return ['required', 'string', 'in:'.implode(',', $this->supportedLocales())];
    }

    /**
     * @return list<string>
     */
    public function supportedLocales(): array
    {
        $configured = config('app.supported_locales', [config('app.locale', 'en')]);

        $locales = array_values(array_unique(array_filter(
            array_map(static fn (mixed $value): string => trim((string) $value), \is_array($configured) ? $configured : []),
            static fn (string $value): bool => $value !== ''
        )));

        if ($locales === []) {
            $fallback = trim((string) config('app.locale', 'en'));

            return [$fallback !== '' ? $fallback : 'en'];
        }

        return $locales;
    }

    /**
     * @return list<array{code:string,label:string}>
     */
    public function availableLocales(): array
    {
        $configured = config('app.available_locales', []);
        $supported = $this->supportedLocales();
        $options = [];

        foreach (\is_array($configured) ? $configured : [] as $entry) {
            if (\is_array($entry)) {
                $value = trim((string) ($entry['value'] ?? $entry['locale'] ?? $entry['code'] ?? ''));
                $label = trim((string) ($entry['label'] ?? $entry['name'] ?? ''));
            } else {
                $value = trim((string) $entry);
                $label = $value;
            }

            if ($value === '' || ! \in_array($value, $supported, true)) {
                continue;
            }

            $options[] = [
                'code' => $value,
                'label' => $label !== '' ? $label : strtoupper($value),
            ];
        }

        if ($options !== []) {
            return array_values($options);
        }

        return array_map(
            static fn (string $locale): array => [
                'code' => $locale,
                'label' => strtoupper($locale),
            ],
            $supported
        );
    }

    public function isSupported(mixed $locale): bool
    {
        if (! \is_string($locale)) {
            return false;
        }

        return \in_array(trim($locale), $this->supportedLocales(), true);
    }

    public function fallbackLocale(): string
    {
        $configured = trim((string) config('app.locale', 'en'));

        if ($this->isSupported($configured)) {
            return $configured;
        }

        return $this->supportedLocales()[0];
    }

    /**
     * @return array{locale:string,source:string}
     */
    public function resolveForRequest(Request $request): array
    {
        $userLocale = $request->user()?->locale;

        if ($userLocale !== null && $userLocale !== '') {
            return $this->resolution($userLocale, 'user');
        }

        $sessionLocale = $request->session()->get('locale');

        if ($sessionLocale !== null && $sessionLocale !== '') {
            return $this->resolution($sessionLocale, 'session');
        }

        $requestedLocale = $request->query('locale');

        if ($requestedLocale !== null && $requestedLocale !== '') {
            return $this->resolution($requestedLocale, 'query');
        }

        $preferredLocale = $request->getPreferredLanguage($this->supportedLocales());

        if ($preferredLocale !== null && $preferredLocale !== '') {
            return $this->resolution($preferredLocale, 'accept-language');
        }

        return [
            'locale' => $this->fallbackLocale(),
            'source' => 'config',
        ];
    }

    public function applyLocale(string $locale): void
    {
        $appliedLocale = $this->isSupported($locale) ? $locale : $this->fallbackLocale();

        app()->setLocale($appliedLocale);
        Carbon::setLocale($appliedLocale);
    }

    public function persistResolvedLocale(Request $request, string $locale): void
    {
        $safeLocale = $this->isSupported($locale) ? $locale : $this->fallbackLocale();
        $user = $request->user();

        if ($user !== null) {
            if ($user->locale !== $safeLocale) {
                $user->forceFill(['locale' => $safeLocale])->save();
            }

            return;
        }

        $request->session()->put('locale', $safeLocale);
    }

    public function persistManualLocale(Request $request, string $locale): void
    {
        $safeLocale = $this->isSupported($locale) ? $locale : $this->fallbackLocale();
        $user = $request->user();

        if ($user !== null) {
            $user->update(['locale' => $safeLocale]);

            return;
        }

        $request->session()->put('locale', $safeLocale);
    }

    /**
     * @return array{locale:string,source:string}
     */
    private function resolution(mixed $locale, string $source): array
    {
        if (! $this->isSupported($locale)) {
            return [
                'locale' => $this->fallbackLocale(),
                'source' => $source,
            ];
        }

        return [
            'locale' => trim((string) $locale),
            'source' => $source,
        ];
    }
}
