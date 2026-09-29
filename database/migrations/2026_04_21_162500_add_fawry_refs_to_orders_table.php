<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'fawry_merchant_ref_num')) {
                // Merchant reference number we send to Fawry (used to match webhooks back to orders)
                $table->string('fawry_merchant_ref_num', 64)->nullable()->after('payment_method');
                $table->index('fawry_merchant_ref_num', 'orders_fawry_merchant_ref_num_index');
            }

            if (! Schema::hasColumn('orders', 'fawry_ref_number')) {
                // Fawry reference number assigned by Fawry (shown to customer / used at POS)
                $table->string('fawry_ref_number', 64)->nullable()->after('fawry_merchant_ref_num');
                $table->index('fawry_ref_number', 'orders_fawry_ref_number_index');
            }

            if (! Schema::hasColumn('orders', 'fawry_payment_reference_number')) {
                // Payment reference number (may be missing on some notification types)
                $table->string('fawry_payment_reference_number', 64)->nullable()->after('fawry_ref_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_fawry_merchant_ref_num_index');
            $table->dropIndex('orders_fawry_ref_number_index');
            $table->dropColumn(['fawry_merchant_ref_num', 'fawry_ref_number', 'fawry_payment_reference_number']);
        });
    }
};
