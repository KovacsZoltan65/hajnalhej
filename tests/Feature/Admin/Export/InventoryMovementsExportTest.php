<?php

use App\Data\Export\ExportRequestData;
use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportType;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Services\Export\ExportManager;
use Illuminate\Support\Facades\Storage;

it('inventory movements csv tartalmazza a fejlécet', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $ingredient = Ingredient::factory()->create(['name' => 'Vaj']);
    InventoryMovement::query()->create([
        'ingredient_id' => $ingredient->id,
        'movement_type' => InventoryMovement::TYPE_ADJUSTMENT_IN,
        'direction' => InventoryMovement::DIRECTION_IN,
        'quantity' => 2,
        'occurred_at' => now(),
        'created_by' => $user->id,
    ]);

    $job = app(ExportManager::class)->createJob(new ExportRequestData(ExportType::InventoryMovements, ExportFormat::Csv), $user);
    app(ExportManager::class)->run($job);

    $content = Storage::disk('local')->get($job->refresh()->path);

    expect($content)->toContain('Movement Type')
        ->and($content)->toContain('Reference ID')
        ->and($content)->toContain('Vaj');
});
