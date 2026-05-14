<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180)->unique()->comment('Beszállító neve');
            $table->string('email')->nullable()->comment('Beszállító email címe');
            $table->string('phone', 64)->nullable()->comment('Beszállító telefonszáma');
            $table->string('tax_number', 64)->nullable()->comment('Beszállító adószáma');
            $table->text('notes')->nullable()->comment('Beszállító megjegyzése');
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
