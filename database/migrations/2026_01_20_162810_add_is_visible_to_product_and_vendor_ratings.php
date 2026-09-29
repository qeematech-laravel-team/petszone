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
        Schema::table('product_ratings', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true)->after('comment');
        });

        Schema::table('vendor_ratings', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true)->after('comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_ratings', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });

        Schema::table('vendor_ratings', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
