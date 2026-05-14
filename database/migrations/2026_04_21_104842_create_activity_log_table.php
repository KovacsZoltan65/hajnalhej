<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable()->index()->comment('Audit napló neve');
            $table->text('description')->comment('Audit napló leírása');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable()->comment('Audit esemény típusa');
            $table->nullableMorphs('causer', 'causer');
            $table->json('attribute_changes')->nullable()->comment('Auditált attribútumváltozások JSON adatai');
            $table->json('properties')->nullable()->comment('Audit esemény kiegészítő JSON adatai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
