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
            if (!Schema::hasColumn('orders', 'course_id')) {
                $table->string('course_id', 40)->nullable()->after('user_id');
                $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');
            }
            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status', 20)->default('pending')->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn(['course_id', 'status']);
        });
    }
};
