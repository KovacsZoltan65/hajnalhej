<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use App\Services\CustomerRegistrationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(private readonly CustomerRegistrationService $service)
    {
    }

    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterCustomerRequest $request): RedirectResponse
    {
        $user = $this->service->register($request->validated());

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
