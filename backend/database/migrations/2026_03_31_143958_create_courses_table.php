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
        Schema::create('courses', function (Blueprint $table) {
            $table->string('course_id', 40)->primary();
            $table->string('title', 255)->notNullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->notNullable();
            $table->string('difficulty', 40)->nullable();
            $table->string('language', 40)->nullable();
            $table->string('created_by', 40)->notNullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
