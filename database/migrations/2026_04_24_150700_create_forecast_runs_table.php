<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('run_number', 64)->unique();
            $table->string('forecast_type', 48)->index();
            $table->string('status', 32)->default('pending')->index();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->unsignedInteger('horizon_days');
            $table->decimal('confidence_percent', 8, 4)->nullable();
            $table->json('parameters')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['forecast_type', 'period_start', 'period_end'], 'forecast_runs_type_period_index');
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_runs');
    }
};
