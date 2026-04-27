<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forecast_run_id')->nullable()->constrained('forecast_runs')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->string('recommendation_number', 64)->unique();
            $table->string('status', 32)->default('draft')->index();
            $table->date('recommendation_date')->index();
            $table->date('needed_by_date')->nullable()->index();
            $table->decimal('estimated_total', 12, 2)->default(0);
            $table->decimal('cashflow_score', 8, 4)->nullable();
            $table->decimal('margin_score', 8, 4)->nullable();
            $table->text('rationale')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'recommendation_date']);
            $table->index(['supplier_id', 'needed_by_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_recommendations');
    }
};
