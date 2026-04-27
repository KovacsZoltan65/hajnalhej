<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Audit\UserActivityAuditService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * @param UserActivityAuditService $auditService
     */
    public function __construct(private readonly UserActivityAuditService $auditService)
    {
    }

    /**
     * @param EmailVerificationRequest $request
     * @return RedirectResponse
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if (! $request->user()?->hasVerifiedEmail()) {
            $request->fulfill();
            if ($request->user() !== null) {
                $this->auditService->logEmailVerified($request->user(), [
                    'operation' => 'auth.email.verify',
                    'source' => 'verification.link',
                ]);
            }
        }

        return redirect()
            ->route('account')
            ->with('success', __('auth_ui.verification.success'));
    }
}
