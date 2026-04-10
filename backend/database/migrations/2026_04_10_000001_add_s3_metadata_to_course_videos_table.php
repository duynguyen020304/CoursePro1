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
        Schema::table('course_videos', function (Blueprint $table) {
            $table->string('url', 2048)->nullable()->change();
            $table->string('storage_disk')->default('s3')->after('url');
            $table->string('storage_bucket')->nullable()->after('storage_disk');
            $table->string('storage_key')->nullable()->after('storage_bucket')->index();
            $table->string('mime_type')->nullable()->after('storage_key');
            $table->unsignedBigInteger('file_size_bytes')->nullable()->after('mime_type');
            $table->string('upload_status')->default('ready')->after('file_size_bytes');
            $table->string('upload_id')->nullable()->after('upload_status');
            $table->string('original_filename')->nullable()->after('upload_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_videos', function (Blueprint $table) {
            $table->dropIndex(['storage_key']);
            $table->dropColumn([
                'storage_disk',
                'storage_bucket',
                'storage_key',
                'mime_type',
                'file_size_bytes',
                'upload_status',
                'upload_id',
                'original_filename',
            ]);
            $table->string('url', 255)->nullable(false)->change();
        });
    }
};
