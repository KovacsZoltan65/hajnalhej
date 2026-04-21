<?php

namespace App\Repositories;

use App\Models\User;

class CustomerRegistrationRepository
{
    /**
     * @param array<string, mixed> $payload
     */
    public function createCustomer(array $payload): User
    {
        return User::query()->create($payload);
    }
}
