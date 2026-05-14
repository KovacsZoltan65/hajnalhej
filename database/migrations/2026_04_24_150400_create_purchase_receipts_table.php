<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_id')->comment('Kapcsolódó beszerzés')->constrained('purchases')->cascadeOnDelete();
            $table->string('receipt_number', 64)->unique()->comment('Beszerzési átvétel Receipt number');
            $table->date('received_date')->index()->comment('Beszerzési átvétel dátuma');
            $table->string('status', 32)->default('draft')->index()->comment('Beszerzési átvétel státusza');
            $table->decimal('total_received_value', 12, 2)->default(0)->comment('Átvett tételek összértéke');
            $table->text('notes')->nullable()->comment('Beszerzési átvétel megjegyzése');
            $table->foreignId('received_by')->nullable()->comment('Beszerzési átvétel Received by')->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable()->index()->comment('Beszerzési átvétel időpontja');
            $table->timestamps();

            $table->index(['purchase_id', 'status']);
            $table->index(['received_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipts');
    }
};
