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
        Schema::table('branch_product_stocks', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('quantity');
            $table->decimal('discount', 10, 2)->nullable()->after('price');
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable()->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_product_stocks', function (Blueprint $table) {
            $table->dropColumn(['price', 'discount', 'discount_type']);
        });
    }
};
