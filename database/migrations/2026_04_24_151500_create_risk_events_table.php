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
            $table->foreignId('supplier_id')->nullable()->comment('Kapcsolódó beszállító')->constrained('suppliers')->nullOnDelete();
            $table->foreignId('ingredient_id')->nullable()->comment('Kapcsolódó alapanyag')->constrained('ingredients')->nullOnDelete();
            $table->string('event_type', 64)->index()->comment('Kockázati esemény típusa');
            $table->string('severity', 24)->default('medium')->index()->comment('Kockázati esemény Severity');
            $table->string('status', 32)->default('open')->index()->comment('Kockázati esemény státusza');
            $table->date('event_date')->index()->comment('Kockázati esemény dátuma');
            $table->decimal('estimated_impact_amount', 12, 2)->nullable()->comment('Estimated impact amount');
            $table->decimal('probability_percent', 8, 4)->nullable()->comment('Probability percent');
            $table->string('title', 180)->comment('Kockázati esemény címe');
            $table->text('description')->nullable()->comment('Kockázati esemény leírása');
            $table->text('mitigation_plan')->nullable()->comment('Kockázati esemény Mitigation plan');
            $table->timestamp('resolved_at')->nullable()->index()->comment('Kockázati esemény időpontja');
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
