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
            $table->string('run_number', 64)->unique()->comment('Előrejelzés futtatás Run number');
            $table->string('forecast_type', 48)->index()->comment('Előrejelzés futtatás típusa');
            $table->string('status', 32)->default('pending')->index()->comment('Előrejelzés futtatás státusza');
            $table->date('period_start')->index()->comment('Előrejelzés futtatás Period start');
            $table->date('period_end')->index()->comment('Előrejelzés futtatás Period end');
            $table->unsignedInteger('horizon_days')->comment('Horizon days');
            $table->decimal('confidence_percent', 8, 4)->nullable()->comment('Confidence percent');
            $table->json('parameters')->nullable()->comment('Előrejelzés futtatási paraméterei JSON formátumban');
            $table->timestamp('started_at')->nullable()->index()->comment('Előrejelzés futtatásának kezdete');
            $table->timestamp('completed_at')->nullable()->index()->comment('Előrejelzés futtatásának vége');
            $table->foreignId('created_by')->nullable()->comment('Előrejelzést indító felhasználó')->constrained('users')->nullOnDelete();
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
