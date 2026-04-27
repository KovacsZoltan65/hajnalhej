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
            $table->string('name', 160)->unique();
            $table->string('code', 32)->unique();
            $table->string('type', 48)->default('bakery')->index();
            $table->string('email')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('address')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
