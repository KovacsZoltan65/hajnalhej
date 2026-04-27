<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('alert_type', 48)->index();
            $table->string('status', 32)->default('open')->index();
            $table->decimal('previous_unit_cost', 12, 2)->nullable();
            $table->decimal('current_unit_cost', 12, 2);
            $table->decimal('change_percent', 8, 4)->nullable();
            $table->decimal('margin_impact', 12, 2)->nullable();
            $table->date('detected_on')->index();
            $table->text('notes')->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();

            $table->index(['ingredient_id', 'detected_on']);
            $table->index(['status', 'detected_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_alerts');
    }
};
