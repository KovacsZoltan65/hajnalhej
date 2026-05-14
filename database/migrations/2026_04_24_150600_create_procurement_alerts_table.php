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
            $table->foreignId('ingredient_id')->nullable()->comment('Kapcsolódó alapanyag')->constrained('ingredients')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->comment('Kapcsolódó beszállító')->constrained('suppliers')->nullOnDelete();
            $table->string('alert_type', 48)->index()->comment('Beszerzési riasztás típusa');
            $table->string('severity', 24)->default('medium')->index()->comment('Beszerzési riasztás Severity');
            $table->string('status', 32)->default('open')->index()->comment('Beszerzési riasztás státusza');
            $table->date('alert_date')->index()->comment('Beszerzési riasztás dátuma');
            $table->decimal('quantity_gap', 12, 3)->nullable()->comment('Hiányzó mennyiség');
            $table->decimal('estimated_cash_impact', 12, 2)->nullable()->comment('Beszerzési riasztás Estimated cash impact');
            $table->string('title', 180)->comment('Beszerzési riasztás címe');
            $table->text('message')->nullable()->comment('Beszerzési riasztás üzenete');
            $table->json('context')->nullable()->comment('Beszerzési riasztás döntési kontextusa JSON formátumban');
            $table->timestamp('resolved_at')->nullable()->index()->comment('Beszerzési riasztás lezárásának időpontja');
            $table->foreignId('resolved_by')->nullable()->comment('Beszerzési riasztás Resolved by')->constrained('users')->nullOnDelete();
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
