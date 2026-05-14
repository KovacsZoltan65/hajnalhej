<?php

use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportStatus;
use App\Enums\Export\ExportType;
use App\Jobs\RunExportJob;
use App\Models\ExportJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

it('jogosultsaggal export job letrejon es queue job indul', function (): void {
    Queue::fake();
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/exports', [
        'type' => ExportType::Products->value,
        'format' => ExportFormat::Csv->value,
        'filters' => ['search' => 'kenyer'],
    ])->assertRedirect();

    $this->assertDatabaseHas('export_jobs', [
        'type' => ExportType::Products->value,
        'format' => ExportFormat::Csv->value,
        'status' => ExportStatus::Pending->value,
        'created_by' => $user->id,
    ]);

    Queue::assertPushed(RunExportJob::class);
});

it('invalid type es format validacios hiba', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/exports', [
        'type' => 'table_dump',
        'format' => 'pdf',
    ])->assertSessionHasErrors(['type', 'format']);
});

it('exports prune torli a lejart completed fajlokat es expired statuszt ad', function (): void {
    $user = User::factory()->create();
    Storage::fake('local');
    Storage::disk('local')->put('exports/old.csv', 'x');

    $job = ExportJob::query()->create([
        'type' => ExportType::Products,
        'format' => ExportFormat::Csv,
        'status' => ExportStatus::Completed,
        'disk' => 'local',
        'path' => 'exports/old.csv',
        'filename' => 'old.csv',
        'created_by' => $user->id,
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('exports:prune')->assertSuccessful();

    expect($job->refresh()->status)->toBe(ExportStatus::Expired)
        ->and(Storage::disk('local')->exists('exports/old.csv'))->toBeFalse();
});
