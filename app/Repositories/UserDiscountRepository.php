<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserDiscount;
use Illuminate\Support\Collection;

class UserDiscountRepository
{
    public function create(User $user, array $data): UserDiscount
    {
        return $user->discounts()->create($data);
    }

    public function update(UserDiscount $discount, array $data): UserDiscount
    {
        $discount->update($data);

        return $discount->refresh();
    }

    public function deactivate(UserDiscount $discount): UserDiscount
    {
        $discount->update(['active' => false]);

        return $discount->refresh();
    }

    public function currentForUser(User $user): Collection
    {
        return $user->discounts()
            ->currentlyValid()
            ->orderByDesc('created_at')
            ->get();
    }
}
