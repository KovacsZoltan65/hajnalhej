<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user === null ? null : [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_admin' => $user->isAdmin(),
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                ],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'ui' => [
                'nav' => [
                    'login' => __('auth_ui.nav.login'),
                    'register' => __('auth_ui.nav.register'),
                    'account' => __('auth_ui.nav.account'),
                    'logout' => __('auth_ui.nav.logout'),
                    'admin' => __('auth_ui.nav.admin'),
                    'cart' => __('commerce.nav.cart'),
                ],
                'register' => [
                    'title' => __('auth_ui.register.title'),
                    'subtitle' => __('auth_ui.register.subtitle'),
                ],
                'commerce' => __('commerce'),
            ],
            'cart' => app(CartService::class)->getCartPayload()['summary'],
        ];
    }
}
