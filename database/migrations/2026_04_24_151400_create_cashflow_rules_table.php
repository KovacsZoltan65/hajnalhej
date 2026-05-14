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
            $table->string('name', 160)->comment('Cashflow szabály neve');
            $table->string('rule_type', 48)->index()->comment('Cashflow szabály típusa');
            $table->decimal('threshold_amount', 12, 2)->nullable()->comment('Threshold amount');
            $table->decimal('warning_percent', 8, 4)->nullable()->comment('Warning percent');
            $table->unsignedInteger('lookahead_days')->default(14)->comment('Lookahead days');
            $table->string('action', 64)->comment('Javasolt művelet');
            $table->boolean('active')->default(true)->index()->comment('Aktív-e');
            $table->unsignedInteger('priority')->default(0)->index()->comment('Szabály prioritása');
            $table->json('conditions')->nullable()->comment('Cashflow szabály feltételei JSON formátumban');
            $table->text('notes')->nullable()->comment('Cashflow szabály megjegyzése');
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
