<?php

use App\Models\ProductionPlan;
use Database\Seeders\DatabaseSeeder;

it('production plan seeder timeline step rekordokat general', function (): void {
    $this->seed(DatabaseSeeder::class);

    $plans = ProductionPlan::query()->with('steps')->get();

    expect($plans->isNotEmpty())->toBeTrue();
    expect($plans->sum(fn (ProductionPlan $plan): int => $plan->steps->count()))->toBeGreaterThan(0);
});

it('seedelt production plan summary mezoinek erteke konzisztens', function (): void {
    $this->seed(DatabaseSeeder::class);

    $plan = ProductionPlan::query()->where('plan_number', 'SEED-PP-MORNING-001')->firstOrFail();

    expect($plan->total_active_minutes)->toBeGreaterThan(0);
    expect($plan->total_wait_minutes)->toBeGreaterThan(0);
    expect($plan->total_recipe_minutes)->toBeGreaterThan(0);
    expect($plan->planned_start_at)->not->toBeNull();
});

it('seedelt plan starter dependency step-eket is tartalmaz', function (): void {
    $this->seed(DatabaseSeeder::class);

    $plan = ProductionPlan::query()->where('plan_number', 'SEED-PP-MORNING-001')->firstOrFail();

    $dependencyCount = $plan->steps()->where('is_dependency', true)->count();
    expect($dependencyCount)->toBeGreaterThan(0);
});

it('seedelt plan timeline relacioja nem ures betolteskor', function (): void {
    $this->seed(DatabaseSeeder::class);

    $plan = ProductionPlan::query()->where('plan_number', 'SEED-PP-MORNING-002')->firstOrFail();
    $plan->load('steps');

    expect($plan->steps->isNotEmpty())->toBeTrue();
});

