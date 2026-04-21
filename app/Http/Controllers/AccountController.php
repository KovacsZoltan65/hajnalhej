<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Account/Index', [
            'account' => [
                'name' => (string) $request->user()?->name,
                'email' => (string) $request->user()?->email,
                'is_verified' => $request->user()?->hasVerifiedEmail() ?? false,
                'role' => (string) $request->user()?->role,
            ],
        ]);
    }
}
