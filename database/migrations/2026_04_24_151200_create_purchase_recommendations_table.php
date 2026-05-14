<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forecast_run_id')->nullable()->comment('Kapcsolódó előrejelzés futtatás')->constrained('forecast_runs')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->comment('Kapcsolódó beszállító')->constrained('suppliers')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->comment('Kapcsolódó beszerzés')->constrained('purchases')->nullOnDelete();
            $table->string('recommendation_number', 64)->unique()->comment('Beszerzési javaslat Recommendation number');
            $table->string('status', 32)->default('draft')->index()->comment('Beszerzési javaslat státusza');
            $table->date('recommendation_date')->index()->comment('Beszerzési javaslat dátuma');
            $table->date('needed_by_date')->nullable()->index()->comment('Beszerzési javaslat dátuma');
            $table->decimal('estimated_total', 12, 2)->default(0)->comment('Estimated total');
            $table->decimal('cashflow_score', 8, 4)->nullable()->comment('Cashflow pontszám');
            $table->decimal('margin_score', 8, 4)->nullable()->comment('Árrés pontszám');
            $table->text('rationale')->nullable()->comment('Beszerzési javaslat Rationale');
            $table->foreignId('created_by')->nullable()->comment('Beszerzési javaslatot létrehozó felhasználó')->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'recommendation_date']);
            $table->index(['supplier_id', 'needed_by_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_recommendations');
    }
};
