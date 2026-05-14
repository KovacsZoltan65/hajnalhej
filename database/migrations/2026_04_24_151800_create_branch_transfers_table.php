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
            $table->foreignId('from_branch_id')->comment('Kapcsolódó forrás telephely')->constrained('branches')->restrictOnDelete();
            $table->foreignId('to_branch_id')->comment('Kapcsolódó cél telephely')->constrained('branches')->restrictOnDelete();
            $table->foreignId('ingredient_id')->comment('Kapcsolódó alapanyag')->constrained('ingredients')->restrictOnDelete();
            $table->string('transfer_number', 64)->unique()->comment('Telephelyközi átadás Transfer number');
            $table->string('status', 32)->default('draft')->index()->comment('Telephelyközi átadás státusza');
            $table->decimal('quantity', 12, 3)->comment('Mennyiség');
            $table->string('unit', 16)->comment('Telephelyközi átadás mértékegysége');
            $table->date('requested_date')->index()->comment('Telephelyközi átadás dátuma');
            $table->date('transferred_date')->nullable()->index()->comment('Telephelyközi átadás dátuma');
            $table->text('notes')->nullable()->comment('Telephelyközi átadás megjegyzése');
            $table->foreignId('requested_by')->nullable()->comment('Telephelyközi átadás Requested by')->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->comment('Telephelyközi átadás Completed by')->constrained('users')->nullOnDelete();
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
