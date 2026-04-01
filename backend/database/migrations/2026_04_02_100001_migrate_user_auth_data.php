<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Copy authentication data from users to user_accounts
        DB::table('users')->get()->each(function ($user) {
            DB::table('user_accounts')->insert([
                'user_id' => $user->user_id,
                'provider' => 'email',
                'provider_account_id' => null,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'remember_token' => $user->remember_token,
                'is_deleted' => false,
                'is_verified' => $user->email_verified_at !== null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback for data migration - manual intervention required
        // This preserves user_accounts data for safety
    }
};