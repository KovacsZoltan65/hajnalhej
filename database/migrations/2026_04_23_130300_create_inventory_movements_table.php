<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->comment('Kapcsolódó alapanyag')->constrained('ingredients')->restrictOnDelete();
            $table->string('movement_type', 48)->index()->comment('Készletmozgás típusa');
            $table->string('direction', 8)->index()->comment('Készletmozgás iránya');
            $table->decimal('quantity', 14, 3)->comment('Mennyiség');
            $table->decimal('unit_cost', 14, 4)->nullable()->comment('Egységköltség');
            $table->decimal('total_cost', 14, 2)->nullable()->comment('Teljes költség');
            $table->timestamp('occurred_at')->index()->comment('Készletmozgás időpontja');
            $table->string('reference_type', 64)->nullable()->index()->comment('Kapcsolódó üzleti objektum típusa');
            $table->unsignedBigInteger('reference_id')->nullable()->index()->comment('Kapcsolódó üzleti objektum azonosítója');
            $table->text('notes')->nullable()->comment('Készletmozgás megjegyzése');
            $table->foreignId('created_by')->nullable()->comment('Mozgást rögzítő felhasználó')->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ingredient_id', 'occurred_at']);
            $table->index(['movement_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
