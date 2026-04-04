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
        Schema::table('course_requirements', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('requirement');
            $table->timestamp('updated_at')->nullable()->after('is_active');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_requirements', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'updated_at']);
            $table->dropSoftDeletes();
        });
    }
};
