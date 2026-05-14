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
            $table->string('fulfillment_method', 32)->default('pickup')->after('pickup_time_slot')->index()->comment('Rendelés teljesítési módja');
            $table->foreignId('pickup_branch_id')
                ->nullable()
                ->after('fulfillment_method')
                ->comment('Kapcsolódó átvételi telephely')->constrained('branches')
                ->nullOnDelete();
            $table->json('billing_address_snapshot')->nullable()->after('pickup_branch_id')->comment('Számlázási cím pillanatképe JSON formátumban');
            $table->json('shipping_address_snapshot')->nullable()->after('billing_address_snapshot')->comment('Szállítási cím pillanatképe JSON formátumban');
            $table->text('delivery_notes')->nullable()->after('shipping_address_snapshot')->comment('Rendelés megjegyzése');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('delivery_notes')->comment('Kiszállítási díj');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['pickup_branch_id']);
            $table->dropIndex(['fulfillment_method']);
            $table->dropColumn([
                'fulfillment_method',
                'pickup_branch_id',
                'billing_address_snapshot',
                'shipping_address_snapshot',
                'delivery_notes',
                'delivery_fee',
            ]);
        });
    }
};
