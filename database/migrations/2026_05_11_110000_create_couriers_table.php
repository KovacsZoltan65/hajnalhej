<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->comment('Futár neve');
            $table->string('phone', 50)->nullable()->comment('Futár telefonszáma');
            $table->string('email')->nullable()->comment('Futár email címe');
            $table->string('vehicle_type', 50)->nullable()->index()->comment('Futár típusa');
            $table->boolean('active')->default(true)->index()->comment('Aktív-e');
            $table->text('notes')->nullable()->comment('Futár megjegyzése');
            $table->json('meta')->nullable()->comment('Futár kiegészítő JSON adatai');
            $table->timestamps();

            $table->comment('Kiszállítást végző futárok törzsadatai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
