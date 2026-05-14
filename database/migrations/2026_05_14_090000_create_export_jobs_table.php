<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->comment('Export típusa');
            $table->string('format')->comment('Export fájlformátuma');
            $table->string('status')->default('pending')->comment('Export feldolgozási státusza');
            $table->json('filters')->nullable()->comment('Export szűrési feltételei JSON formátumban');
            $table->string('disk')->nullable()->comment('Export fájlt tároló lemez');
            $table->string('path')->nullable()->comment('Export fájl tárolási útvonala');
            $table->string('filename')->nullable()->comment('Export letöltési fájlneve');
            $table->unsignedBigInteger('rows_total')->nullable()->comment('Exportált sorok száma');
            $table->foreignId('created_by')->comment('Exportot indító felhasználó')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at')->nullable()->comment('Export indításának időpontja');
            $table->timestamp('finished_at')->nullable()->comment('Export befejezésének időpontja');
            $table->timestamp('expires_at')->nullable()->comment('Export letöltési lejárata');
            $table->text('error_message')->nullable()->comment('Export hibaüzenete');
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['created_by', 'created_at']);
            $table->comment('Admin export műveletek és letölthető állományok naplója');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_jobs');
    }
};
