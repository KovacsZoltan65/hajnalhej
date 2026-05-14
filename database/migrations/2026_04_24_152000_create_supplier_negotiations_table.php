<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_negotiations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->comment('Kapcsolódó beszállító')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->nullable()->comment('Kapcsolódó alapanyag')->constrained('ingredients')->nullOnDelete();
            $table->string('status', 32)->default('planned')->index()->comment('Beszállítói tárgyalás státusza');
            $table->date('planned_on')->nullable()->index()->comment('Beszállítói tárgyalás dátuma');
            $table->date('completed_on')->nullable()->index()->comment('Beszállítói tárgyalás dátuma');
            $table->decimal('current_unit_cost', 12, 2)->nullable()->comment('Aktuális egységköltség');
            $table->decimal('target_unit_cost', 12, 2)->nullable()->comment('Cél egységköltség');
            $table->decimal('expected_savings', 12, 2)->nullable()->comment('Várható megtakarítás');
            $table->decimal('achieved_savings', 12, 2)->nullable()->comment('Elért megtakarítás');
            $table->text('talking_points')->nullable()->comment('Tárgyalási pontok');
            $table->text('outcome_notes')->nullable()->comment('Beszállítói tárgyalás megjegyzése');
            $table->json('evidence_snapshot')->nullable()->comment('Beszállítói tárgyalás bizonyítékainak pillanatképe JSON formátumban');
            $table->foreignId('owner_id')->nullable()->comment('Kapcsolódó felelős felhasználó')->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier_id', 'status']);
            $table->index(['ingredient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_negotiations');
    }
};
