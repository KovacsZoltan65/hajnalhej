<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PermissionRegistry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ProLoadTestCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customerRole = Role::query()
            ->where('name', PermissionRegistry::ROLE_CUSTOMER)
            ->where('guard_name', 'web')
            ->first();

        for ($i = 1; $i <= 10; $i++) {
            $user = User::query()->updateOrCreate(
                ['email' => sprintf('loadtest.customer%02d@hajnalhej.test', $i)],
                [
                    'name' => sprintf('Load Test Ügyfél %02d', $i),
                    'password' => Hash::make('password'),
                    'email_verified_at' => Carbon::now(),
                ],
            );

            if ($customerRole && ! $user->hasRole($customerRole->name)) {
                $user->assignRole($customerRole->name);
            }
        }
    }
}
