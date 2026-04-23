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
            $table->string('event_key', 120)->index();
            $table->string('funnel', 80)->nullable()->index();
            $table->string('step', 80)->nullable();
            $table->string('cta_id', 120)->nullable()->index();
            $table->string('hero_variant', 60)->nullable()->index();
            $table->string('source', 40)->default('backend')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 120)->nullable()->index();
            $table->string('path', 255)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversion_events');
    }
};

