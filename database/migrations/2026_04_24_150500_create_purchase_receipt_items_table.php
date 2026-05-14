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
            $table->foreignId('purchase_receipt_id')->comment('Kapcsolódó beszerzési átvétel')->constrained('purchase_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->comment('Kapcsolódó beszerzési tétel')->constrained('purchase_items')->nullOnDelete();
            $table->foreignId('ingredient_id')->comment('Kapcsolódó alapanyag')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('ordered_quantity', 12, 3)->default(0)->comment('Rendelt mennyiség');
            $table->decimal('received_quantity', 12, 3)->comment('Received quantity');
            $table->decimal('rejected_quantity', 12, 3)->default(0)->comment('Rejected quantity');
            $table->string('unit', 16)->comment('Beszerzési átvételi tétel mértékegysége');
            $table->decimal('unit_cost', 12, 2)->comment('Egységköltség');
            $table->decimal('line_total', 12, 2)->comment('Tétel sorösszege');
            $table->string('quality_status', 32)->default('accepted')->index()->comment('Beszerzési átvételi tétel státusza');
            $table->text('notes')->nullable()->comment('Beszerzési átvételi tétel megjegyzése');
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
