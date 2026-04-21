<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isAdmin()) {
            $target = $user === null ? route('login') : route('account');

            /** @var RedirectResponse $redirect */
            $redirect = redirect()->to($target);

            return $redirect->with('error', __('auth_ui.nav.admin_only'));
        }

        return $next($request);
    }
}
