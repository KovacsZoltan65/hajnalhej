<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashflow_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('rule_type', 48)->index();
            $table->decimal('threshold_amount', 12, 2)->nullable();
            $table->decimal('warning_percent', 8, 4)->nullable();
            $table->unsignedInteger('lookahead_days')->default(14);
            $table->string('action', 64);
            $table->boolean('active')->default(true)->index();
            $table->unsignedInteger('priority')->default(0)->index();
            $table->json('conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('name');
            $table->index(['active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashflow_rules');
    }
};
