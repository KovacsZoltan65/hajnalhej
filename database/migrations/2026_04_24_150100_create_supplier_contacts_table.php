<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->comment('Kapcsolódó beszállító')->constrained('suppliers')->cascadeOnDelete();
            $table->string('name', 160)->comment('Beszállítói kapcsolattartó neve');
            $table->string('role', 120)->nullable()->comment('Beszállítói kapcsolattartó szerepköre');
            $table->string('email')->nullable()->comment('Beszállítói kapcsolattartó email címe');
            $table->string('phone', 64)->nullable()->comment('Beszállítói kapcsolattartó telefonszáma');
            $table->boolean('is_primary')->default(false)->index()->comment('Is primary-e');
            $table->boolean('active')->default(true)->index()->comment('Aktív-e');
            $table->text('notes')->nullable()->comment('Beszállítói kapcsolattartó megjegyzése');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier_id', 'active']);
            $table->index(['supplier_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_contacts');
    }
};
