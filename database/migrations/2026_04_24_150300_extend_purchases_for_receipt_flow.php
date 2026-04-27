<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->date('expected_delivery_date')->nullable()->index()->after('purchase_date');
            $table->date('received_date')->nullable()->index()->after('expected_delivery_date');
            $table->string('receipt_status', 32)->default('not_received')->index()->after('status');
            $table->decimal('received_total', 14, 2)->default(0)->after('total');
            $table->timestamp('ordered_at')->nullable()->index()->after('posted_at');
            $table->timestamp('cancelled_at')->nullable()->index()->after('ordered_at');

            $table->index(['supplier_id', 'status', 'expected_delivery_date'], 'purchases_supplier_status_eta_index');
            $table->index(['receipt_status', 'received_date'], 'purchases_receipt_status_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropIndex('purchases_supplier_status_eta_index');
            $table->dropIndex('purchases_receipt_status_date_index');
            $table->dropColumn([
                'expected_delivery_date',
                'received_date',
                'receipt_status',
                'received_total',
                'ordered_at',
                'cancelled_at',
            ]);
        });
    }
};
