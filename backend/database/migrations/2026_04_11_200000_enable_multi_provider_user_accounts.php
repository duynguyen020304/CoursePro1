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
        if (!Schema::hasColumn('user_accounts', 'id')) {
            Schema::table('user_accounts', function (Blueprint $table) {
                $table->uuid('id')->nullable()->after('user_id');
            });
        }

        DB::table('user_accounts')
            ->select('user_id')
            ->whereNull('id')
            ->orderBy('user_id')
            ->chunk(100, function ($accounts) {
                foreach ($accounts as $account) {
                    DB::table('user_accounts')
                        ->where('user_id', $account->user_id)
                        ->whereNull('id')
                        ->update(['id' => (string) Str::uuid()]);
                }
            });

        DB::statement('ALTER TABLE refresh_tokens DROP CONSTRAINT IF EXISTS refresh_tokens_user_id_foreign');
        DB::statement('ALTER TABLE email_verification_tokens DROP CONSTRAINT IF EXISTS email_verification_tokens_user_id_foreign');
        DB::statement('ALTER TABLE password_reset_tokens DROP CONSTRAINT IF EXISTS password_reset_tokens_new_user_id_foreign');
        DB::statement('ALTER TABLE password_reset_tokens DROP CONSTRAINT IF EXISTS password_reset_tokens_user_id_foreign');

        DB::statement('ALTER TABLE user_accounts DROP CONSTRAINT IF EXISTS user_accounts_user_id_unique');
        DB::statement('ALTER TABLE user_accounts DROP CONSTRAINT IF EXISTS user_accounts_email_unique');
        DB::statement('ALTER TABLE user_accounts DROP CONSTRAINT IF EXISTS oauth_provider_unique');
        DB::statement('ALTER TABLE user_accounts DROP CONSTRAINT IF EXISTS user_accounts_pkey');
        DB::statement('ALTER TABLE user_accounts ALTER COLUMN id SET NOT NULL');
        DB::statement('ALTER TABLE user_accounts ADD PRIMARY KEY (id)');

        DB::statement('CREATE INDEX IF NOT EXISTS user_accounts_user_id_index ON user_accounts (user_id)');
        DB::statement('DROP INDEX IF EXISTS user_accounts_email_provider_active_unique');

        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS oauth_provider_unique
            ON user_accounts (provider, provider_account_id)
            WHERE provider_account_id IS NOT NULL AND deleted_at IS NULL
        ");

        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS user_accounts_email_provider_active_unique
            ON user_accounts (email)
            WHERE provider = 'email' AND email IS NOT NULL AND deleted_at IS NULL
        ");

        DB::statement('ALTER TABLE refresh_tokens ADD CONSTRAINT refresh_tokens_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE email_verification_tokens ADD CONSTRAINT email_verification_tokens_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE password_reset_tokens ADD CONSTRAINT password_reset_tokens_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE refresh_tokens DROP CONSTRAINT IF EXISTS refresh_tokens_user_id_foreign');
        DB::statement('ALTER TABLE email_verification_tokens DROP CONSTRAINT IF EXISTS email_verification_tokens_user_id_foreign');
        DB::statement('ALTER TABLE password_reset_tokens DROP CONSTRAINT IF EXISTS password_reset_tokens_user_id_foreign');
        DB::statement('ALTER TABLE password_reset_tokens DROP CONSTRAINT IF EXISTS password_reset_tokens_new_user_id_foreign');

        DB::statement('DROP INDEX IF EXISTS user_accounts_email_provider_active_unique');
        DB::statement('DROP INDEX IF EXISTS oauth_provider_unique');
        DB::statement('DROP INDEX IF EXISTS user_accounts_user_id_index');
        DB::statement('ALTER TABLE user_accounts DROP CONSTRAINT IF EXISTS user_accounts_pkey');

        if (Schema::hasColumn('user_accounts', 'id')) {
            Schema::table('user_accounts', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }

        DB::statement('ALTER TABLE user_accounts ADD PRIMARY KEY (user_id)');
        DB::statement('ALTER TABLE user_accounts ADD CONSTRAINT user_accounts_user_id_unique UNIQUE (user_id)');
        DB::statement('ALTER TABLE user_accounts ADD CONSTRAINT user_accounts_email_unique UNIQUE (email)');
        DB::statement('ALTER TABLE user_accounts ADD CONSTRAINT oauth_provider_unique UNIQUE (provider, provider_account_id)');

        DB::statement('ALTER TABLE refresh_tokens ADD CONSTRAINT refresh_tokens_user_id_foreign FOREIGN KEY (user_id) REFERENCES user_accounts(user_id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE email_verification_tokens ADD CONSTRAINT email_verification_tokens_user_id_foreign FOREIGN KEY (user_id) REFERENCES user_accounts(user_id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE password_reset_tokens ADD CONSTRAINT password_reset_tokens_user_id_foreign FOREIGN KEY (user_id) REFERENCES user_accounts(user_id) ON DELETE CASCADE');
    }
};
