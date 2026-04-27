<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipt_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_receipt_id')->constrained('purchase_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->nullOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('ordered_quantity', 12, 3)->default(0);
            $table->decimal('received_quantity', 12, 3);
            $table->decimal('rejected_quantity', 12, 3)->default(0);
            $table->string('unit', 16);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->string('quality_status', 32)->default('accepted')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_receipt_id', 'ingredient_id'], 'pri_receipt_ingredient_index');
            $table->index(['ingredient_id', 'quality_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_items');
    }
};
