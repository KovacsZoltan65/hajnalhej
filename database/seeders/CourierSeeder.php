<?php

namespace Database\Seeders;

use App\Enums\Delivery\VehicleType;
use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    public function run(): void
    {
        $couriers = [
            [
                'name' => 'Hajnal Gyalogos Futár',
                'phone' => '+36301111111',
                'email' => 'gyalogos.futar@hajnalhej.hu',
                'status' => Courier::STATUS_ACTIVE,
                'vehicle_type' => VehicleType::WALKING->value,
                'active' => true,
                'notes' => 'Belvárosi rövid távokhoz.',
            ],
            [
                'name' => 'Hajnal Biciklis Futár',
                'phone' => '+36302222222',
                'email' => 'biciklis.futar@hajnalhej.hu',
                'status' => Courier::STATUS_ACTIVE,
                'vehicle_type' => VehicleType::BICYCLE->value,
                'active' => true,
                'notes' => 'Gyors városi kiszállításokhoz.',
            ],
            [
                'name' => 'Hajnal Autós Futár',
                'phone' => '+36303333333',
                'email' => 'autos.futar@hajnalhej.hu',
                'status' => Courier::STATUS_ACTIVE,
                'vehicle_type' => VehicleType::CAR->value,
                'active' => true,
                'notes' => 'Nagyobb vagy távolabbi rendelésekhez.',
            ],
            [
                'name' => 'Archivált Futár',
                'phone' => '+36304444444',
                'email' => 'archivalt.futar@hajnalhej.hu',
                'status' => Courier::STATUS_INACTIVE,
                'vehicle_type' => VehicleType::SCOOTER->value,
                'active' => false,
                'notes' => 'Teszt inaktív futár.',
            ],
        ];

        foreach ($couriers as $courier) {
            Courier::query()->updateOrCreate(
                ['email' => $courier['email']],
                $courier,
            );
        }
    }
}
