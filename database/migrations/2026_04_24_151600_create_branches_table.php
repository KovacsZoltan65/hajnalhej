<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique()->comment('Telephely neve');
            $table->string('code', 50)->unique()->comment('Telephely kódja');
            $table->string('type', 48)->default('bakery')->index()->comment('Telephely típusa');
            $table->string('email')->nullable()->comment('Telephely email címe');
            $table->string('phone', 50)->nullable()->comment('Telephely telefonszáma');
            $table->string('address', 500)->nullable()->comment('Telephely Address');
            $table->boolean('active')->default(true)->index()->comment('Aktív-e');
            $table->json('meta')->nullable()->comment('Telephely kiegészítő JSON adatai');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'type']);
            $table->comment('Telephelyek, üzletek és raktárak');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
