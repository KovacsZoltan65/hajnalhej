<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\LocaleSettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function __construct(
        private readonly LocaleSettingsService $localeSettings,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $resolution = $this->localeSettings->resolveForRequest($request);

        $this->localeSettings->applyLocale($resolution['locale']);
        $this->localeSettings->persistResolvedLocale($request, $resolution['locale']);

        return $next($request);
    }
}
