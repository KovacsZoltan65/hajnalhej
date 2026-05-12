<?php

namespace App\Http\Controllers;

use App\Support\InertiaPage;
use App\Support\PermissionRegistry;
use Illuminate\Http\Request;
use Inertia\Response;

class AccountController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()?->can(PermissionRegistry::ACCOUNT_VIEW), 403);

        return InertiaPage::ACCOUNT_INDEX->render([
            'account' => [
                'name' => (string) $request->user()?->name,
                'email' => (string) $request->user()?->email,
                'is_verified' => $request->user()?->hasVerifiedEmail() ?? false,
                'roles' => $request->user()?->getRoleNames()->values()->all() ?? [],
            ],
        ]);
    }
}
