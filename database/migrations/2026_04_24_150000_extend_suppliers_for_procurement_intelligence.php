<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            $table->unsignedInteger('lead_time_days')->default(0)->change();
            $table->decimal('minimum_order_value', 12, 2)->nullable()->after('lead_time_days');
            $table->boolean('active')->default(true)->index()->after('minimum_order_value');
            $table->string('currency', 3)->default('HUF')->after('active');
            $table->json('meta')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropColumn(['minimum_order_value', 'active', 'currency', 'meta']);
            $table->unsignedSmallInteger('lead_time_days')->nullable()->change();
        });
    }
};
