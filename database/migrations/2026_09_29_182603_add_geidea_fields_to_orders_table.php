<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('geidea_payment_intent_id')->nullable()->index()->after('fawry_payment_reference_number');
            $table->string('geidea_order_id')->nullable()->index()->after('geidea_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('geidea_payment_intent_id');
            $table->dropIndex('geidea_order_id');
            $table->dropColumn(['geidea_payment_intent_id', 'geidea_order_id']);
        });
    }
};
