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
        Schema::create('payments', function (Blueprint $table) {
            $table->string('payment_id', 40)->primary();
            $table->string('order_id', 40)->notNullable();
            $table->timestamp('payment_date')->useCurrent();
            $table->string('payment_method', 50)->notNullable();
            $table->string('payment_status', 20)->default('pending');
            $table->decimal('amount', 10, 2)->notNullable();

            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
