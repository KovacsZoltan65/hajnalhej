<?php

namespace Database\Seeders;

use App\Data\Branches\BranchType;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Hajnalhéj Központi Pékség',
                'code' => 'BAKERY-01',
                'type' => BranchType::BAKERY,
                'email' => 'pekseg@hajnalhej.hu',
                'phone' => '+36 30 100 1001',
                'address' => '1111 Budapest, Kovász utca 1.',
            ],
            [
                'name' => 'Hajnalhéj Belvárosi Üzlet',
                'code' => 'SHOP-01',
                'type' => BranchType::SHOP,
                'email' => 'belvaros@hajnalhej.hu',
                'phone' => '+36 30 100 1002',
                'address' => '1052 Budapest, Reggel tér 4.',
            ],
            [
                'name' => 'Hajnalhéj Átvételi Pont',
                'code' => 'PICKUP-01',
                'type' => BranchType::PICKUP_POINT,
                'email' => 'atvetel@hajnalhej.hu',
                'phone' => '+36 30 100 1003',
                'address' => '1024 Budapest, Ropogós köz 8.',
            ],
            [
                'name' => 'Hajnalhéj Alapanyag Raktár',
                'code' => 'WAREHOUSE-01',
                'type' => BranchType::WAREHOUSE,
                'email' => 'raktar@hajnalhej.hu',
                'phone' => '+36 30 100 1004',
                'address' => '2040 Budaörs, Liszt Ferenc utca 12.',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::query()->updateOrCreate(
                ['code' => $branch['code']],
                [
                    ...$branch,
                    'active' => true,
                    'meta' => null,
                ],
            );
        }
    }
}
