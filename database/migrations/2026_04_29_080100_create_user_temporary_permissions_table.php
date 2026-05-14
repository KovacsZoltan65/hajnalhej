<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_temporary_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->comment('Kapcsolódó felhasználó')->constrained()->cascadeOnDelete();
            $table->string('permission_name')->comment('Ideiglenes jogosultság neve');
            $table->timestamp('starts_at')->nullable()->comment('Ideiglenes jogosultság kezdete');
            $table->timestamp('expires_at')->nullable()->comment('Ideiglenes jogosultság lejárata');
            $table->text('reason')->nullable()->comment('Ideiglenes jogosultság indoklása');
            $table->foreignId('granted_by')->nullable()->comment('Jogosultságot adó felhasználó')->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable()->comment('Ideiglenes jogosultság visszavonásának időpontja');
            $table->timestamps();

            $table->index(['user_id', 'permission_name']);
            $table->index(['revoked_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_temporary_permissions');
    }
};
