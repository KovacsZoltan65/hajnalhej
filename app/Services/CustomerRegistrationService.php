<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CustomerRegistrationRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerRegistrationService
{
    public function __construct(private readonly CustomerRegistrationRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function register(array $payload): User
    {
        $user = DB::transaction(function () use ($payload): User {
            $user = $this->repository->createCustomer([
                'name' => trim((string) $payload['name']),
                'email' => mb_strtolower(trim((string) $payload['email'])),
                'password' => (string) $payload['password'],
                'role' => User::ROLE_CUSTOMER,
            ]);

            event(new Registered($user));

            return $user;
        });

        Auth::login($user);

        return $user;
    }
}
