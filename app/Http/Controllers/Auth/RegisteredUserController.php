<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use App\Services\ConversionTrackingService;
use App\Services\CustomerRegistrationService;
use App\Support\ConversionEventRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly CustomerRegistrationService $service,
        private readonly ConversionTrackingService $conversionTrackingService,
    ) {}

    public function create(Request $request): Response
    {
        $this->conversionTrackingService->trackBackendEvent(
            eventKey: ConversionEventRegistry::REGISTRATION_VIEWED,
            request: $request,
            funnel: 'registration',
            step: 'view',
        );

        return Inertia::render('Auth/Register');
    }

    public function store(RegisterCustomerRequest $request): RedirectResponse
    {
        $user = $this->service->register($request->validated(), $request->session()->getId());

        if (! $user->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with('success', __('auth_ui.register.success_verify_required'));
        }

        return redirect()
            ->route('account')
            ->with('success', __('auth_ui.register.success'));
    }
}
