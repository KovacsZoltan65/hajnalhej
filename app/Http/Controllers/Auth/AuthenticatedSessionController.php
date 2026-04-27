<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Audit\UserActivityAuditService;
use App\Support\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * @param UserActivityAuditService $auditService
     */
    public function __construct(private readonly UserActivityAuditService $auditService)
    {
    }

    /**
     * @return \Inertia\Response
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        if ($user !== null) {
            $this->auditService->logLogin($user, [
                'operation' => 'auth.login',
                'guard' => 'web',
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        }

        $fallbackRoute = $user?->can(PermissionRegistry::ADMIN_PANEL_ACCESS) ? 'admin.dashboard' : 'account';

        return redirect()
            ->intended(route($fallbackRoute))
            ->with('success', __('auth_ui.login.success'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $this->auditService->logLogout($user, [
                'operation' => 'auth.logout',
                'guard' => 'web',
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('info', __('auth_ui.login.logout_success'));
    }
}
