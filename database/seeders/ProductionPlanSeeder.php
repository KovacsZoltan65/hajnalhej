<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductionPlan;
use App\Models\User;
use App\Services\ProductionPlanService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var ProductionPlanService $service */
        $service = app(ProductionPlanService::class);

        $user = User::query()->first() ?? User::factory()->create([
            'name' => 'Hajnalhej Seeder Admin',
            'email' => 'seeder-admin@hajnalhej.hu',
        ]);

        $catalog = Product::query()
            ->whereIn('slug', [
                'egyszeru-kovaszos-feher-kenyer',
                'magas-hidracioju-kezmuves-kenyer',
                'klasszikus-kovaszos-kenyer',
                'magvas-vekni',
            ])
            ->where('is_active', true)
            ->get()
            ->keyBy('slug');

        if ($catalog->isEmpty()) {
            return;
        }

        $blueprints = [
            [
                'plan_number' => 'SEED-PP-MORNING-001',
                'target_ready_at' => Carbon::tomorrow()->setTime(8, 0)->toDateTimeString(),
                'status' => ProductionPlan::STATUS_CALCULATED,
                'notes' => '[seed] Reggeli nyitasra tervezett gyartas (timeline demo).',
                'items' => [
                    ['slug' => 'egyszeru-kovaszos-feher-kenyer', 'target_quantity' => 20, 'unit_label' => 'db'],
                    ['slug' => 'magas-hidracioju-kezmuves-kenyer', 'target_quantity' => 12, 'unit_label' => 'db'],
                ],
            ],
            [
                'plan_number' => 'SEED-PP-MORNING-002',
                'target_ready_at' => Carbon::tomorrow()->addDay()->setTime(7, 30)->toDateTimeString(),
                'status' => ProductionPlan::STATUS_DRAFT,
                'notes' => '[seed] Kovetkezo napi gyartasi terv ellenorzesi celra.',
                'items' => [
                    ['slug' => 'klasszikus-kovaszos-kenyer', 'target_quantity' => 16, 'unit_label' => 'db'],
                    ['slug' => 'magvas-vekni', 'target_quantity' => 14, 'unit_label' => 'db'],
                ],
            ],
        ];

        foreach ($blueprints as $blueprint) {
            $items = collect($blueprint['items'])
                ->values()
                ->map(function (array $item, int $index) use ($catalog): ?array {
                    $product = $catalog->get($item['slug']);

                    if (! $product instanceof Product) {
                        return null;
                    }

                    return [
                        'product_id' => $product->id,
                        'target_quantity' => $item['target_quantity'],
                        'unit_label' => $item['unit_label'] ?? 'db',
                        'sort_order' => $index,
                    ];
                })
                ->filter()
                ->values()
                ->all();

            if ($items === []) {
                continue;
            }

            $payload = [
                'target_ready_at' => $blueprint['target_ready_at'],
                'status' => $blueprint['status'],
                'notes' => $blueprint['notes'],
                'is_locked' => false,
                'items' => $items,
            ];

            $existing = ProductionPlan::query()
                ->where('plan_number', $blueprint['plan_number'])
                ->first();

            if ($existing instanceof ProductionPlan) {
                $service->update($existing, $payload);
                continue;
            }

            $created = $service->create($payload, (int) $user->id);
            $created->update([
                'plan_number' => $blueprint['plan_number'],
            ]);
        }
    }
}

