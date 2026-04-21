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
        Schema::create('production_plan_steps', function (Blueprint $table): void {
            $table->id()->comment('Rekord azonosito');
            $table->foreignId('production_plan_id')->constrained('production_plans')->cascadeOnDelete()->comment('Kapcsolodo gyartasi terv azonosito');
            $table->foreignId('production_plan_item_id')->nullable()->constrained('production_plan_items')->nullOnDelete()->comment('Kapcsolodo gyartasi tetel azonosito');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->comment('Erintett termek azonosito');
            $table->foreignId('depends_on_product_id')->nullable()->constrained('products')->nullOnDelete()->comment('A termek, amelyikhez dependency-kent keszul');
            $table->string('title')->comment('Timeline lepes megnevezese');
            $table->string('step_type')->index()->comment('Lepes tipusa');
            $table->text('description')->nullable()->comment('Lepes reszletes leirasa');
            $table->dateTime('starts_at')->index()->comment('Lepes kezdete');
            $table->dateTime('ends_at')->index()->comment('Lepes vege');
            $table->unsignedInteger('duration_minutes')->default(0)->comment('Aktiv ido percben');
            $table->unsignedInteger('wait_minutes')->default(0)->comment('Varakozasi ido percben');
            $table->unsignedInteger('sort_order')->default(0)->index()->comment('Globalis timeline sorrend');
            $table->string('timeline_group', 120)->nullable()->comment('Csoport azonosito (pl. termek vagy starter)');
            $table->boolean('is_dependency')->default(false)->index()->comment('Dependency lepes-e (starter/kovasz)');
            $table->json('meta')->nullable()->comment('Kiegeszito technikai adatok');
            $table->timestamps();

            $table->index(['production_plan_id', 'starts_at']);
            $table->index(['production_plan_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_steps');
    }
};

