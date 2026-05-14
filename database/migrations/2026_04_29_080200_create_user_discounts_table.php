<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_discounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->comment('Kapcsolódó felhasználó')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->comment('Felhasználói kedvezmény típusa');
            $table->decimal('value', 10, 2)->comment('Kedvezmény értéke');
            $table->timestamp('starts_at')->nullable()->comment('Felhasználói kedvezmény kezdete');
            $table->timestamp('expires_at')->nullable()->comment('Felhasználói kedvezmény lejárata');
            $table->boolean('active')->default(true)->index()->comment('Aktív-e');
            $table->text('reason')->nullable()->comment('Felhasználói kedvezmény indoklása');
            $table->foreignId('created_by')->nullable()->comment('Kedvezményt létrehozó felhasználó')->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'active']);
            $table->index(['starts_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_discounts');
    }
};
