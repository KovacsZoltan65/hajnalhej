<?php

namespace App\Http\Controllers;

use App\Data\Settings\SettingSaveValueData;
use App\Services\LocaleSettingsService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreferenceController extends Controller
{
    public function __construct(
        private readonly LocaleSettingsService $localeSettings,
        private readonly SettingsService $settingsService,
    ) {}

    public function setLocale(Request $request): Response
    {
        $request->validate(['locale' => $this->localeSettings->validationRule()]);
        $locale = (string) $request->input('locale');

        app()->setLocale($locale);
        $request->setLocale($locale);

        return response()->noContent();
    }
}