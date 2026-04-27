<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('from_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->string('transfer_number', 64)->unique();
            $table->string('status', 32)->default('draft')->index();
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 16);
            $table->date('requested_date')->index();
            $table->date('transferred_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['from_branch_id', 'status']);
            $table->index(['to_branch_id', 'status']);
            $table->index(['ingredient_id', 'requested_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_transfers');
    }
};
