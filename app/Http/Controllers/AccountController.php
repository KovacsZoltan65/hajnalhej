<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Support\PermissionRegistry;

class AccountController extends Controller
{
    /**
     * @param Request $request
     * @return \Inertia\Response
     */
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()?->can(PermissionRegistry::ACCOUNT_VIEW), 403);

        return Inertia::render('Account/Index', [
            'account' => [
                'name' => (string) $request->user()?->name,
                'email' => (string) $request->user()?->email,
                'is_verified' => $request->user()?->hasVerifiedEmail() ?? false,
                'roles' => $request->user()?->getRoleNames()->values()->all() ?? [],
            ],
        ]);
    }
}
