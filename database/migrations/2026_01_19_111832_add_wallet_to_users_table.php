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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('wallet', 10, 2)->default(0)->after('image');
            $table->decimal('points', 10, 2)->default(0)->after('wallet');
            $table->string('referral_code')->unique()->nullable()->after('points');
            $table->foreignId('referred_by_id')->nullable()->constrained('users')->nullOnDelete()->after('referral_code');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet');
            $table->dropColumn('points');
            $table->dropColumn('referral_code');
            $table->dropForeign(['referred_by_id']);
            $table->dropColumn('referred_by_id');
        });
    }
};
