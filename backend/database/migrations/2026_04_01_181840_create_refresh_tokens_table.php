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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('token', 255); // HMAC-SHA256 hashed, never raw
            $table->timestamp('expires_at');
            $table->string('ip_address', 45)->nullable(); // IPv4/IPv6
            $table->text('user_agent')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->boolean('is_deleted')->default(false); // soft delete
            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('user_accounts')
                ->onDelete('cascade');

            // Index for quick lookup of valid tokens by user
            $table->index(['user_id', 'is_revoked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};