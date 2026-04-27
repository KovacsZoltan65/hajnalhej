<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_supplier_terms', function (Blueprint $table): void {
            $table->boolean('active')->default(true)->index()->after('preferred');
            $table->softDeletes()->after('updated_at');

            $table->index(['ingredient_id', 'active', 'preferred'], 'ist_ingredient_active_preferred_index');
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_supplier_terms', function (Blueprint $table): void {
            $table->dropIndex('ist_ingredient_active_preferred_index');
            $table->dropColumn('active');
            $table->dropSoftDeletes();
        });
    }
};
