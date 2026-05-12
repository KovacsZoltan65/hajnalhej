<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\InertiaPage;
use Illuminate\Http\Request;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return InertiaPage::AUTH_VERIFY_EMAIL->render([
            'isVerified' => $request->user()?->hasVerifiedEmail() ?? false,
        ]);
    }
}
