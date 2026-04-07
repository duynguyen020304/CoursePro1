<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'slug')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });

            DB::table('categories')->orderBy('id')->get()->each(function ($category) {
                DB::table('categories')
                    ->where('id', $category->id)
                    ->update(['slug' => Str::slug($category->name)]);
            });

            Schema::table('categories', function (Blueprint $table) {
                $table->unique('slug');
            });
        }

        if (! Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('parent_id', 36)->nullable()->after('slug');
            });
        }

        Schema::create('categories_uuid_tmp', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name', 255);
            $table->string('slug')->unique();
            $table->string('parent_id', 36)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        $categories = DB::table('categories')->orderBy('id')->get();
        $idMap = [];

        foreach ($categories as $category) {
            $idMap[$category->id] = (string) Str::uuid();
        }

        foreach ($categories as $category) {
            DB::table('categories_uuid_tmp')->insert([
                'id' => $idMap[$category->id],
                'name' => $category->name,
                'slug' => $category->slug ?: Str::slug($category->name),
                'parent_id' => $category->parent_id ? ($idMap[$category->parent_id] ?? null) : null,
                'sort_order' => $category->sort_order ?? 0,
                'is_active' => $category->is_active ?? true,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'deleted_at' => $category->deleted_at,
            ]);
        }

        Schema::create('course_category_uuid_tmp', function (Blueprint $table) {
            $table->string('course_id', 40);
            $table->string('category_id', 36);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->primary(['course_id', 'category_id']);
        });

        $courseCategories = DB::table('course_category')->get();

        foreach ($courseCategories as $pivot) {
            DB::table('course_category_uuid_tmp')->insert([
                'course_id' => $pivot->course_id,
                'category_id' => $idMap[$pivot->category_id],
                'is_active' => $pivot->is_active ?? true,
                'is_deleted' => $pivot->is_deleted ?? false,
                'created_at' => $pivot->created_at,
            ]);
        }

        Schema::drop('course_category');
        Schema::drop('categories');

        Schema::rename('categories_uuid_tmp', 'categories');
        Schema::rename('course_category_uuid_tmp', 'course_category');

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });

        Schema::table('course_category', function (Blueprint $table) {
            $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::create('categories_int_tmp', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug')->nullable()->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        $categories = DB::table('categories')->orderBy('sort_order')->get();
        $idMap = [];
        $nextId = 1;

        foreach ($categories as $category) {
            $idMap[$category->id] = $nextId++;
        }

        foreach ($categories as $category) {
            DB::table('categories_int_tmp')->insert([
                'id' => $idMap[$category->id],
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => $category->parent_id ? ($idMap[$category->parent_id] ?? null) : null,
                'sort_order' => $category->sort_order ?? 0,
                'is_active' => $category->is_active ?? true,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'deleted_at' => $category->deleted_at,
            ]);
        }

        Schema::create('course_category_int_tmp', function (Blueprint $table) {
            $table->string('course_id', 40);
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->primary(['course_id', 'category_id']);
        });

        $courseCategories = DB::table('course_category')->get();

        foreach ($courseCategories as $pivot) {
            DB::table('course_category_int_tmp')->insert([
                'course_id' => $pivot->course_id,
                'category_id' => $idMap[$pivot->category_id],
                'is_active' => $pivot->is_active ?? true,
                'is_deleted' => $pivot->is_deleted ?? false,
                'created_at' => $pivot->created_at,
            ]);
        }

        Schema::drop('course_category');
        Schema::drop('categories');

        Schema::rename('categories_int_tmp', 'categories');
        Schema::rename('course_category_int_tmp', 'course_category');

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });

        Schema::table('course_category', function (Blueprint $table) {
            $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }
};
