<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            if (! Schema::hasColumn('ingredients', 'average_unit_cost')) {
                $table->decimal('average_unit_cost', 14, 4)->nullable()->after('estimated_unit_cost');
            }

            if (! Schema::hasColumn('ingredients', 'stock_value')) {
                $table->decimal('stock_value', 14, 2)->nullable()->after('average_unit_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            $drops = [];
            foreach (['average_unit_cost', 'stock_value'] as $column) {
                if (Schema::hasColumn('ingredients', $column)) {
                    $drops[] = $column;
                }
            }

            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
