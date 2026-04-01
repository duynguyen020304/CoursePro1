<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'slug')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });

            // Backfill existing categories with slugs
            Category::each(function ($category) {
                $category->update(['slug' => Str::slug($category->name)]);
            });

            // Make slug unique after backfilling
            Schema::table('categories', function (Blueprint $table) {
                $table->unique('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('categories', 'slug')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }
    }
};
