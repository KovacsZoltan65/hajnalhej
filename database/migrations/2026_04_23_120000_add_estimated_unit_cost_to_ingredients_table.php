<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            if (! Schema::hasColumn('ingredients', 'estimated_unit_cost')) {
                $table->decimal('estimated_unit_cost', 12, 4)
                    ->default(0)
                    ->after('unit')
                    ->comment('Becsült egységköltség a mértékegységre vetítve');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            if (Schema::hasColumn('ingredients', 'estimated_unit_cost')) {
                $table->dropColumn('estimated_unit_cost');
            }
        });
    }
};

