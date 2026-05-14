<?php

use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportStatus;
use App\Enums\Export\ExportType;
use App\Models\ExportJob;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function createExportJobForDownload(User $user, ExportStatus $status = ExportStatus::Completed): ExportJob
{
    Storage::disk('local')->put("exports/test-{$user->id}.csv", "Header\nValue\n");

    return ExportJob::query()->create([
        'type' => ExportType::Products,
        'format' => ExportFormat::Csv,
        'status' => $status,
        'disk' => 'local',
        'path' => "exports/test-{$user->id}.csv",
        'filename' => "test-{$user->id}.csv",
        'created_by' => $user->id,
        'expires_at' => now()->addDay(),
    ]);
}

it('completed export letoltheto', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $job = createExportJobForDownload($user);

    $this->actingAs($user)->get("/admin/exports/{$job->id}/download")->assertOk();
});

it('pending es failed export nem letoltheto', function (ExportStatus $status): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $job = createExportJobForDownload($user, $status);

    $this->actingAs($user)->get("/admin/exports/{$job->id}/download")->assertNotFound();
})->with([ExportStatus::Pending, ExportStatus::Failed]);

it('mas user exportja sima userkent nem letoltheto', function (): void {
    Storage::fake('local');
    $owner = User::factory()->create();
    $other = User::factory()->customer()->create();
    $job = createExportJobForDownload($owner);

    $this->actingAs($other)->get("/admin/exports/{$job->id}/download")->assertForbidden();
});

it('admin mas user exportjat letoltheti', function (): void {
    Storage::fake('local');
    $owner = User::factory()->customer()->create();
    $admin = User::factory()->create();
    $job = createExportJobForDownload($owner);

    $this->actingAs($admin)->get("/admin/exports/{$job->id}/download")->assertOk();
});

it('lejart export nem letoltheto', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $job = createExportJobForDownload($user);
    $job->update(['expires_at' => now()->subMinute()]);

    $this->actingAs($user)->get("/admin/exports/{$job->id}/download")->assertNotFound();
});
