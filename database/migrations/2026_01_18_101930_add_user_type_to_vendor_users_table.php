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
        Schema::table('vendor_users', function (Blueprint $table) {
            $table->enum('user_type', ['owner', 'branch'])->default('owner')->after('is_active');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade')->after('user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_users', function (Blueprint $table) {
            $table->dropColumn('user_type');
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
