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
        // Fix cart_id column length - need 41 chars for 'cart_' prefix + UUID
        Schema::table('carts', function (Blueprint $table) {
            $table->string('cart_id', 50)->change();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('cart_id', 50)->change();
            $table->string('cart_item_id', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('cart_id', 40)->change();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('cart_id', 40)->change();
            $table->string('cart_item_id', 40)->change();
        });
    }
};