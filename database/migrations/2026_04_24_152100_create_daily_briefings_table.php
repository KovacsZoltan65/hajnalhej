<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_briefings', function (Blueprint $table): void {
            $table->id();
            $table->date('briefing_date')->unique()->comment('Napi vezetői összefoglaló dátuma');
            $table->string('status', 32)->default('draft')->index()->comment('Napi vezetői összefoglaló státusza');
            $table->decimal('cash_needed_today', 12, 2)->default(0)->comment('Mai várható készpénzigény');
            $table->decimal('projected_procurement_total', 12, 2)->default(0)->comment('Várható beszerzési összeg');
            $table->unsignedInteger('open_alerts_count')->default(0)->comment('Nyitott riasztások száma');
            $table->unsignedInteger('critical_alerts_count')->default(0)->comment('Kritikus riasztások száma');
            $table->json('summary')->nullable()->comment('Napi vezetői összefoglaló tartalma JSON formátumban');
            $table->json('recommended_actions')->nullable()->comment('Ajánlott vezetői teendők JSON formátumban');
            $table->timestamp('generated_at')->nullable()->index()->comment('Napi vezetői összefoglaló generálásának időpontja');
            $table->foreignId('generated_by')->nullable()->comment('Összefoglalót generáló felhasználó')->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable()->index()->comment('Napi vezetői összefoglaló tudomásulvételének időpontja');
            $table->foreignId('acknowledged_by')->nullable()->comment('Összefoglalót tudomásul vevő felhasználó')->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'briefing_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_briefings');
    }
};
