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
            $table->foreignId('supplier_id')->comment('Kapcsolódó beszállító')->constrained('suppliers')->cascadeOnDelete();
            $table->date('period_start')->index()->comment('Beszállítói pontszám Period start');
            $table->date('period_end')->index()->comment('Beszállítói pontszám Period end');
            $table->decimal('overall_score', 8, 4)->comment('Overall score');
            $table->decimal('price_score', 8, 4)->nullable()->comment('Ár pontszám');
            $table->decimal('reliability_score', 8, 4)->nullable()->comment('Reliability score');
            $table->decimal('quality_score', 8, 4)->nullable()->comment('Quality score');
            $table->decimal('lead_time_score', 8, 4)->nullable()->comment('Lead time score');
            $table->unsignedInteger('orders_count')->default(0)->comment('Rendelések száma');
            $table->unsignedInteger('late_deliveries_count')->default(0)->comment('Late deliveries count');
            $table->decimal('rejected_quantity', 12, 3)->default(0)->comment('Rejected quantity');
            $table->json('score_breakdown')->nullable()->comment('Beszállítói pontszám részletezése JSON formátumban');
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
