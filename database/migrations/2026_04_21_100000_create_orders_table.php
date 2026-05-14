<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number', 32)->unique()->comment('Egyedi rendelési azonosító');
            $table->foreignId('user_id')->nullable()->comment('Kapcsolódó felhasználó')->constrained()->nullOnDelete();
            $table->string('customer_name', 120)->comment('Rendelő neve');
            $table->string('customer_email', 255)->comment('Rendelő email címe');
            $table->string('customer_phone', 40)->comment('Rendelő telefonszáma');
            $table->string('status', 40)->index()->comment('Rendelés státusza');
            $table->string('currency', 3)->default('HUF')->comment('Rendelés pénzneme');
            $table->decimal('subtotal', 12, 2)->comment('Részösszeg');
            $table->decimal('total', 12, 2)->comment('Végösszeg');
            $table->text('notes')->nullable()->comment('Rendelés megjegyzése');
            $table->date('pickup_date')->nullable()->index()->comment('Átvétel dátuma');
            $table->string('pickup_time_slot', 60)->nullable()->comment('Átvételi idősáv');
            $table->timestamp('placed_at')->nullable()->index()->comment('Rendelés leadásának időpontja');
            $table->timestamp('confirmed_at')->nullable()->comment('Rendelés visszaigazolásának időpontja');
            $table->timestamp('completed_at')->nullable()->comment('Rendelés teljesítésének időpontja');
            $table->timestamp('cancelled_at')->nullable()->comment('Rendelés lemondásának időpontja');
            $table->text('internal_notes')->nullable()->comment('Belső rendelési megjegyzés');
            $table->json('metadata')->nullable()->comment('Rendelés kiegészítő üzleti adatai JSON formátumban');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
