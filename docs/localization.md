# Localization

Hajnalhej uses one server-side locale source of truth for Laravel, Inertia, Vue, PrimeVue, and browser formatting.

## Resolution Flow

```text
Request enters web middleware
        |
        v
SetLocale resolves the first available candidate:
1. authenticated user locale: users.locale
2. session locale: session('locale')
3. request query: ?locale=
4. Accept-Language: getPreferredLanguage(['hu', 'en'])
5. config('app.locale')
        |
        v
Unsupported locale resolves to config('app.locale')
        |
        v
app()->setLocale($locale)
Carbon::setLocale($locale)
        |
        v
Persist resolved locale:
- authenticated: users.locale
- guest: session('locale')
        |
        v
Inertia shares locale and available_locales
```

## Persistence Strategy

Authenticated users store locale in `users.locale`. This is the primary storage and wins over session, query string, and browser language.

Guests store locale in session. On the first visit, the middleware detects `Accept-Language` and writes the supported locale to the session so later requests stay consistent.

Manual selection uses `POST /locale` through the `locale.switch` route. It validates the locale, updates the authenticated user or guest session, then redirects back.

## Adding A Locale

1. Add the locale code to `config/app.php` under `supported_locales`.
2. Add a matching entry to `available_locales` with `code` and `label`.
3. Add translation files under `lang/{locale}.json` and any grouped PHP translation files.
4. Update tests for the new locale where business behavior depends on language.
5. Confirm PrimeVue and `Intl` formatters support the locale code.

## Frontend Source Of Truth

Frontend code must read locale from `$page.props.locale`. Use `resources/js/composables/useLocaleFormat.js` for numbers, currency, dates, and datetimes. Do not hardcode browser locales such as `hu-HU` or `en-US` in Vue files.

`LocaleSwitcher.vue` reads `$page.props.available_locales`, posts to `route('locale.switch')`, and preserves scroll/state during the Inertia request.

## Troubleshooting

If the wrong language appears for a logged-in user, check `users.locale` first. It intentionally overrides session and browser headers.

If a guest sees the wrong language, clear the session cookie and retry with the desired browser `Accept-Language` header.

If an invalid locale appears in session or query string, the middleware falls back to `config('app.locale')` and persists that supported fallback.

If translations are missing, verify both the Laravel `lang` files and the dynamic import path in `resources/js/app.js`.

## Future Architecture

Tenant-level locale can be inserted ahead of session for tenant-owned storefronts, while keeping user locale first for signed-in personal preference.

User profile settings can expose `users.locale` directly, using the same `locale.switch` validation rules.

Admin forced locale can be modeled as a permission-gated override in middleware, placed before user locale only for operational screens.

SEO locale prefixes such as `/hu` and `/en` should be added at the routing layer, with middleware syncing the route prefix into the same locale service.

Translation lazy-loading is already compatible with `laravel-vue-i18n`; larger locales can be split by domain once page bundles grow.

i18n chunk splitting should follow route or module boundaries, for example public commerce, admin operations, and reporting.
