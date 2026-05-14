<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->comment('Kapcsolódó beszállító')->constrained('suppliers')->nullOnDelete();
            $table->string('reference_number', 120)->nullable()->index()->comment('Beszerzési hivatkozási szám');
            $table->date('purchase_date')->index()->comment('Beszerzés dátuma');
            $table->string('status', 32)->default('draft')->index()->comment('Beszerzés státusza');
            $table->decimal('subtotal', 14, 2)->default(0)->comment('Részösszeg');
            $table->decimal('total', 14, 2)->default(0)->comment('Végösszeg');
            $table->text('notes')->nullable()->comment('Beszerzés megjegyzése');
            $table->foreignId('created_by')->nullable()->comment('Beszerzést rögzítő felhasználó')->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable()->index()->comment('Beszerzés könyvelésének időpontja');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
