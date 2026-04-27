<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->decimal('overall_score', 8, 4);
            $table->decimal('price_score', 8, 4)->nullable();
            $table->decimal('reliability_score', 8, 4)->nullable();
            $table->decimal('quality_score', 8, 4)->nullable();
            $table->decimal('lead_time_score', 8, 4)->nullable();
            $table->unsignedInteger('orders_count')->default(0);
            $table->unsignedInteger('late_deliveries_count')->default(0);
            $table->decimal('rejected_quantity', 12, 3)->default(0);
            $table->json('score_breakdown')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'period_start', 'period_end'], 'supplier_scores_period_unique');
            $table->index(['overall_score', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_scores');
    }
};
