<?php

namespace Database\Seeders\test;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoadTestCustomerSeeder extends Seeder
{
    public function run(): void
    {
        for ($i=1;$i<=10;$i++) {
            User::firstOrCreate(
                ['email'=>"customer{$i}@example.com"],
                ['name'=>"Customer {$i}", 'password'=>Hash::make('password')]
            );
        }
    }
}
