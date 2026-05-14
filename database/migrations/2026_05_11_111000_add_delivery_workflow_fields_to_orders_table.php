<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('courier_id')
                ->nullable()
                ->after('delivery_fee')
                ->comment('Kapcsolódó futár')
                ->constrained('couriers')
                ->nullOnDelete();
            $table->string('delivery_status', 50)->nullable()->after('courier_id')->index()->comment('Kiszállítás státusza');
            $table->dateTime('delivery_scheduled_at')->nullable()->after('delivery_status')->index()->comment('Tervezett kiszállítás időpontja');
            $table->dateTime('out_for_delivery_at')->nullable()->after('delivery_scheduled_at')->comment('Futárnak átadás időpontja');
            $table->dateTime('delivered_at')->nullable()->after('out_for_delivery_at')->comment('Kézbesítés időpontja');
            $table->text('failed_delivery_reason')->nullable()->after('delivered_at')->comment('Sikertelen kézbesítés oka');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['courier_id']);
            $table->dropIndex(['delivery_status']);
            $table->dropIndex(['delivery_scheduled_at']);
            $table->dropColumn([
                'courier_id',
                'delivery_status',
                'delivery_scheduled_at',
                'out_for_delivery_at',
                'delivered_at',
                'failed_delivery_reason',
            ]);
        });
    }
};
