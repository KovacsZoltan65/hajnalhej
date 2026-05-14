<?php

use App\Data\Export\ExportRequestData;
use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportType;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Export\ExportManager;
use Illuminate\Support\Facades\Storage;

it('products csv tartalmazza a fejlécet es explicit mezoket', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Kenyerek']);
    Product::factory()->create(['category_id' => $category->id, 'name' => 'Rozskenyer', 'slug' => 'rozskenyer']);

    $job = app(ExportManager::class)->createJob(new ExportRequestData(ExportType::Products, ExportFormat::Csv), $user);
    app(ExportManager::class)->run($job);

    $content = Storage::disk('local')->get($job->refresh()->path);

    expect($content)->toContain('Sort Order')
        ->and($content)->toContain('Updated At')
        ->and($content)->toContain('Rozskenyer')
        ->and($content)->not->toContain('description');
});
