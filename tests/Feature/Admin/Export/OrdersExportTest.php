<?php

use App\Data\Export\ExportRequestData;
use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportType;
use App\Models\Order;
use App\Models\User;
use App\Services\Export\ExportManager;
use Illuminate\Support\Facades\Storage;

it('orders csv tartalmazza a fejlécet es nem exportal belso jegyzetet', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    Order::factory()->create(['customer_name' => 'Teszt Vevő', 'internal_notes' => 'rejtett']);

    $job = app(ExportManager::class)->createJob(new ExportRequestData(ExportType::Orders, ExportFormat::Csv), $user);
    app(ExportManager::class)->run($job);

    $content = Storage::disk('local')->get($job->refresh()->path);

    expect($content)->toContain('Order Number')
        ->and($content)->toContain('Fulfillment Method')
        ->and($content)->toContain('Teszt Vevő')
        ->and($content)->not->toContain('rejtett');
});
