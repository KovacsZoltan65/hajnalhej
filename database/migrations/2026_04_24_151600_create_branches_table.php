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
            $table->string('name')->unique();
            $table->string('code', 50)->unique();
            $table->string('type', 48)->default('bakery')->index();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address', 500)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->json('meta')->nullable();
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
