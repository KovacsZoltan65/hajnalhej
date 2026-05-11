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
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('vehicle_type', 50)->nullable()->index();
            $table->boolean('active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->comment('Kiszállítást végző futárok törzsadatai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
