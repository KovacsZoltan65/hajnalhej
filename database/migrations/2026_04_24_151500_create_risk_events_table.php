<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->string('event_type', 64)->index();
            $table->string('severity', 24)->default('medium')->index();
            $table->string('status', 32)->default('open')->index();
            $table->date('event_date')->index();
            $table->decimal('estimated_impact_amount', 12, 2)->nullable();
            $table->decimal('probability_percent', 8, 4)->nullable();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->text('mitigation_plan')->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'severity', 'event_date']);
            $table->index(['supplier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_events');
    }
};
