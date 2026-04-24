<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('ingredients', 'reorder_level')) {
            DB::table('ingredients')
                ->whereNotNull('reorder_level')
                ->update(['minimum_stock' => DB::raw('reorder_level')]);

            Schema::table('ingredients', function (Blueprint $table): void {
                $table->dropColumn('reorder_level');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            if (! Schema::hasColumn('ingredients', 'reorder_level')) {
                $table->decimal('reorder_level', 12, 3)->nullable()->after('minimum_stock');
            }
        });

        if (Schema::hasColumn('ingredients', 'reorder_level')) {
            DB::table('ingredients')->update(['reorder_level' => DB::raw('minimum_stock')]);
        }
    }
};
