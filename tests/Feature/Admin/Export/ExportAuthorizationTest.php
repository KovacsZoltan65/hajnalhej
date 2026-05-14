<?php

use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportType;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('jogosultsag nelkul 403 export inditasnal', function (): void {
    Queue::fake();
    $user = User::factory()->customer()->create();

    $this->actingAs($user)->post('/admin/exports', [
        'type' => ExportType::Orders->value,
        'format' => ExportFormat::Csv->value,
    ])->assertForbidden();
});

it('admin export indexet eleri', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/exports')->assertOk();
});
