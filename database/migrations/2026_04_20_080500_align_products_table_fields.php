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
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'name')) {
                $table->string('name')->after('category_id')->comment('Megnevezés');
            }

            if (! Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->after('name')->comment('Egyedi URL azonosító, SEO célra');
            }

            if (! Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('slug')->comment('Ár');
            }

            if (! Schema::hasColumn('products', 'short_description')) {
                $table->string('short_description')->nullable()->after('price')->comment('Rövid leírás');
            }

            if (! Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('short_description')->comment('Részletes termékleírás');
            }

            if (! Schema::hasColumn('products', 'image_path')) {
                $table->string('image_path')->nullable()->after('description')->comment('Kép útvonala');
            }

            if (! Schema::hasColumn('products', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->index()->after('image_path')->comment('Admin listázási sorrend');
            }
        });

        if (Schema::hasColumn('products', 'slug')) {
            try {
                Schema::table('products', function (Blueprint $table): void {
                    $table->unique('slug', 'products_slug_unique');
                });
            } catch (\Throwable) {
                // Slug unique index already exists in most environments.
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        try {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropUnique('products_slug_unique');
            });
        } catch (\Throwable) {
            // Keep rollback resilient when the index does not exist.
        }
    }
};
