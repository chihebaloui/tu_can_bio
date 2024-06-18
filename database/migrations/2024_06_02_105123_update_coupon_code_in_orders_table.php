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
            // Change the column type from double to varchar
            $table->string('coupon_code', 255)->nullable()->change();

            // Add the new coupon_code_id column if it doesn't exist
            if (!Schema::hasColumn('orders', 'coupon_code_id')) {
                $table->foreignId('coupon_code_id')->nullable()->after('coupon_code')->constrained()->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert the column type back to double
            $table->double('coupon_code', 10, 2)->nullable()->change();

            // Drop the coupon_code_id column if it exists
            if (Schema::hasColumn('orders', 'coupon_code_id')) {
                $table->dropConstrainedForeignId('coupon_code_id');
            }
        });
    }
};
