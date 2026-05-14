<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversion_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_key', 120)->index()->comment('Konverziós esemény kulcsa');
            $table->string('funnel', 80)->nullable()->index()->comment('Konverziós tölcsér neve');
            $table->string('step', 80)->nullable()->comment('Konverziós lépés neve');
            $table->string('cta_id', 120)->nullable()->index()->comment('Kapcsolódó CTA azonosító');
            $table->string('hero_variant', 60)->nullable()->index()->comment('Hero szakasz variánsa');
            $table->string('source', 40)->default('backend')->index()->comment('Konverziós esemény forrása');
            $table->foreignId('user_id')->nullable()->comment('Kapcsolódó felhasználó')->constrained()->nullOnDelete();
            $table->string('session_id', 120)->nullable()->index()->comment('Kapcsolódó munkamenet');
            $table->string('path', 255)->nullable()->comment('Konverziós esemény útvonala');
            $table->string('url', 500)->nullable()->comment('Konverziós esemény URL címe');
            $table->string('referrer', 500)->nullable()->comment('Konverziós esemény hivatkozó címe');
            $table->string('ip_hash', 64)->nullable()->comment('Konverziós esemény IP hash értéke');
            $table->string('user_agent', 500)->nullable()->comment('Konverziós esemény böngésző azonosítója');
            $table->json('metadata')->nullable()->comment('Konverziós esemény kiegészítő JSON adatai');
            $table->timestamp('occurred_at')->index()->comment('Konverziós esemény időpontja');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversion_events');
    }
};
