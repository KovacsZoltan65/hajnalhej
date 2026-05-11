<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\InertiaPage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render(InertiaPage::AUTH_VERIFY_EMAIL->value, [
            'isVerified' => $request->user()?->hasVerifiedEmail() ?? false,
        ]);
    }
}
