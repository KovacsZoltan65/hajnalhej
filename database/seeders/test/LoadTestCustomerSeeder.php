<?php

namespace Database\Seeders\test;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LoadTestCustomerSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            User::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                ['name' => "Customer {$i}", 'password' => Hash::make('password')]
            );
        }
    }
}
