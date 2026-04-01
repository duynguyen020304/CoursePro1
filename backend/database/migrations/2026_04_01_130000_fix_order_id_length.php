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
        // Fix order_id column length - need 47 chars for 'order_' prefix + UUID
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_id', 50)->change();
        });

        // Also fix payment_id if needed
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_id', 50)->change();
            $table->string('order_id', 50)->change();
        });

        // Fix order_details order_id
        Schema::table('order_details', function (Blueprint $table) {
            $table->string('order_id', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_id', 40)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_id', 40)->change();
            $table->string('order_id', 40)->change();
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->string('order_id', 40)->change();
        });
    }
};