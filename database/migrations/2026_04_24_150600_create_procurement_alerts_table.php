<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('alert_type', 48)->index();
            $table->string('severity', 24)->default('medium')->index();
            $table->string('status', 32)->default('open')->index();
            $table->date('alert_date')->index();
            $table->decimal('quantity_gap', 12, 3)->nullable();
            $table->decimal('estimated_cash_impact', 12, 2)->nullable();
            $table->string('title', 180);
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'severity', 'alert_date']);
            $table->index(['ingredient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_alerts');
    }
};
