<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create new password_reset_tokens table with user_id
        Schema::create('password_reset_tokens_new', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();

            $table->foreign('user_id')->references('user_id')->on('user_accounts')->onDelete('cascade');
        });

        // Migrate existing data
        DB::table('password_resets')->get()->each(function ($reset) {
            $userAccount = DB::table('user_accounts')->where('email', $reset->email)->first();
            if ($userAccount) {
                DB::table('password_reset_tokens_new')->insert([
                    'user_id' => $userAccount->user_id,
                    'token' => $reset->token,
                    'created_at' => $reset->created_at,
                ]);
            }
        });

        // Drop old table and rename new table
        Schema::dropIfExists('password_resets');
        Schema::rename('password_reset_tokens_new', 'password_reset_tokens');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate old structure
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->useCurrent();
        });

        // Migrate data back
        DB::table('password_reset_tokens')->get()->each(function ($reset) {
            $userAccount = DB::table('user_accounts')->where('user_id', $reset->user_id)->first();
            if ($userAccount) {
                DB::table('password_resets')->insert([
                    'email' => $userAccount->email,
                    'token' => $reset->token,
                    'created_at' => $reset->created_at,
                ]);
            }
        });

        Schema::dropIfExists('password_reset_tokens');
    }
};