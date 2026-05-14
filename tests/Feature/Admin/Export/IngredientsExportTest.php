<?php

use App\Data\Export\ExportRequestData;
use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportType;
use App\Models\Ingredient;
use App\Models\User;
use App\Services\Export\ExportManager;
use Illuminate\Support\Facades\Storage;

it('ingredients xlsx fajl letrejon es csv fejlec contract stabil', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    Ingredient::factory()->create(['name' => 'Liszt BL80']);

    $job = app(ExportManager::class)->createJob(new ExportRequestData(ExportType::Ingredients, ExportFormat::Xlsx), $user);
    app(ExportManager::class)->run($job);

    expect(Storage::disk('local')->exists($job->refresh()->path))->toBeTrue()
        ->and($job->rows_total)->toBe(1)
        ->and($job->filename)->toEndWith('.xlsx');
});
