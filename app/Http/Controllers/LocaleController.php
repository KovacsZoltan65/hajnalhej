<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SwitchLocaleRequest;
use App\Services\LocaleSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class LocaleController extends Controller
{
    public function __construct(
        private readonly LocaleSettingsService $localeSettings,
    ) {}

    public function __invoke(SwitchLocaleRequest $request): RedirectResponse|JsonResponse
    {
        $locale = $request->locale();

        $this->localeSettings->persistManualLocale($request, $locale);
        $this->localeSettings->applyLocale($locale);

        if ($request->expectsJson()) {
            return response()->json([
                'locale' => $locale,
                'available_locales' => $this->localeSettings->availableLocales(),
            ]);
        }

        return back();
    }
}
