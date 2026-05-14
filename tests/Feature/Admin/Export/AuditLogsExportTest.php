<?php

use App\Data\Export\ExportRequestData;
use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportType;
use App\Models\User;
use App\Services\Export\ExportManager;
use Illuminate\Support\Facades\Storage;

it('audit logs csv maszkolja a sensitive property mezoket', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();

    activity('security')
        ->causedBy($user)
        ->withProperties(['token' => 'secret-token', 'safe' => 'visible'])
        ->event('login')
        ->log('login');

    $job = app(ExportManager::class)->createJob(new ExportRequestData(ExportType::AuditLogs, ExportFormat::Csv), $user);
    app(ExportManager::class)->run($job);

    $content = Storage::disk('local')->get($job->refresh()->path);

    expect($content)->toContain('Log Name')
        ->and($content)->toContain('Properties JSON')
        ->and($content)->toContain('[masked]')
        ->and($content)->toContain('visible')
        ->and($content)->not->toContain('secret-token');
});
