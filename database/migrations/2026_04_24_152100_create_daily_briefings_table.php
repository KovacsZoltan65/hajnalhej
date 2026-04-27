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
            $table->date('briefing_date')->unique();
            $table->string('status', 32)->default('draft')->index();
            $table->decimal('cash_needed_today', 12, 2)->default(0);
            $table->decimal('projected_procurement_total', 12, 2)->default(0);
            $table->unsignedInteger('open_alerts_count')->default(0);
            $table->unsignedInteger('critical_alerts_count')->default(0);
            $table->json('summary')->nullable();
            $table->json('recommended_actions')->nullable();
            $table->timestamp('generated_at')->nullable()->index();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable()->index();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'briefing_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_briefings');
    }
};
