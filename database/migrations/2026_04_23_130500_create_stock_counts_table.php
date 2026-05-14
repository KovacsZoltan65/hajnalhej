<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_counts', function (Blueprint $table): void {
            $table->id();
            $table->date('count_date')->index()->comment('Leltár dátuma');
            $table->string('status', 32)->default('draft')->index()->comment('Leltár státusza');
            $table->text('notes')->nullable()->comment('Leltár megjegyzése');
            $table->foreignId('created_by')->nullable()->comment('Leltárt rögzítő felhasználó')->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable()->index()->comment('Leltár lezárásának időpontja');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_counts');
    }
};
