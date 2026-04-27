<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_negotiations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->string('status', 32)->default('planned')->index();
            $table->date('planned_on')->nullable()->index();
            $table->date('completed_on')->nullable()->index();
            $table->decimal('current_unit_cost', 12, 2)->nullable();
            $table->decimal('target_unit_cost', 12, 2)->nullable();
            $table->decimal('expected_savings', 12, 2)->nullable();
            $table->decimal('achieved_savings', 12, 2)->nullable();
            $table->text('talking_points')->nullable();
            $table->text('outcome_notes')->nullable();
            $table->json('evidence_snapshot')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier_id', 'status']);
            $table->index(['ingredient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_negotiations');
    }
};
