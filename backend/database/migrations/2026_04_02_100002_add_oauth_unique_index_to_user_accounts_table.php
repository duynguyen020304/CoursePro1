<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add unique constraint for OAuth provider identity linking.
     */
    public function up(): void
    {
        Schema::table('user_accounts', function (Blueprint $table) {
            // Unique constraint for OAuth providers: prevents duplicate OAuth links
            // Note: This allows multiple 'email' provider accounts (different emails)
            // but ensures unique mapping for each OAuth provider+account combination
            $table->unique(['provider', 'provider_account_id'], 'oauth_provider_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_accounts', function (Blueprint $table) {
            $table->dropUnique('oauth_provider_unique');
        });
    }
};