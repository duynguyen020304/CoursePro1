<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserAccount;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GoogleOAuthAccountLinkingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_google_login_links_to_existing_email_account(): void
    {
        $user = $this->createUserWithEmailAccount('learner@example.com');

        [$resolvedUser, $googleAccount, $isNewUser] = app(AuthService::class)->findOrCreateGoogleUser([
            'id' => 'google-sub-1',
            'email' => 'learner@example.com',
            'verified_email' => true,
            'name' => 'Linked Learner',
            'picture' => 'https://example.com/avatar.png',
        ]);

        $this->assertFalse($isNewUser);
        $this->assertSame($user->user_id, $resolvedUser->user_id);
        $this->assertSame(UserAccount::PROVIDER_GOOGLE, $googleAccount->provider);
        $this->assertSame(1, User::where('user_id', $user->user_id)->count());
        $this->assertSame(2, UserAccount::where('user_id', $user->user_id)->count());
        $this->assertDatabaseHas('user_accounts', [
            'user_id' => $user->user_id,
            'provider' => UserAccount::PROVIDER_GOOGLE,
            'provider_account_id' => 'google-sub-1',
            'email' => 'learner@example.com',
        ]);
    }

    public function test_google_provider_lookup_takes_priority_over_matching_email(): void
    {
        $googleUser = $this->createUser('google-owner@example.com');
        $emailUser = $this->createUserWithEmailAccount('shared@example.com');

        $existingGoogleAccount = UserAccount::create([
            'user_id' => $googleUser->user_id,
            'provider' => UserAccount::PROVIDER_GOOGLE,
            'provider_account_id' => 'google-sub-priority',
            'email' => 'shared@example.com',
            'email_verified_at' => now(),
            'is_verified' => true,
        ]);

        [$resolvedUser, $resolvedAccount, $isNewUser] = app(AuthService::class)->findOrCreateGoogleUser([
            'id' => 'google-sub-priority',
            'email' => 'shared@example.com',
            'verified_email' => true,
            'name' => 'Priority User',
        ]);

        $this->assertFalse($isNewUser);
        $this->assertSame($googleUser->user_id, $resolvedUser->user_id);
        $this->assertSame($existingGoogleAccount->id, $resolvedAccount->id);
        $this->assertNotSame($emailUser->user_id, $resolvedUser->user_id);
        $this->assertSame(1, UserAccount::where('user_id', $googleUser->user_id)->count());
        $this->assertSame(1, UserAccount::where('user_id', $emailUser->user_id)->count());
    }

    public function test_google_login_rejects_unverified_email_before_linking(): void
    {
        $user = $this->createUserWithEmailAccount('unverified@example.com');

        try {
            app(AuthService::class)->findOrCreateGoogleUser([
                'id' => 'google-sub-unverified',
                'email' => 'unverified@example.com',
                'verified_email' => false,
                'name' => 'Blocked User',
            ]);

            $this->fail('Expected an HttpException for unverified Google email.');
        } catch (HttpException $exception) {
            $this->assertSame(400, $exception->getStatusCode());
            $this->assertSame('Email not verified by Google.', $exception->getMessage());
        }

        $this->assertSame(1, User::where('user_id', $user->user_id)->count());
        $this->assertSame(1, UserAccount::where('user_id', $user->user_id)->count());
        $this->assertDatabaseMissing('user_accounts', [
            'user_id' => $user->user_id,
            'provider' => UserAccount::PROVIDER_GOOGLE,
            'provider_account_id' => 'google-sub-unverified',
        ]);
    }

    private function createUserWithEmailAccount(string $email): User
    {
        $user = $this->createUser($email);

        UserAccount::create([
            'user_id' => $user->user_id,
            'provider' => UserAccount::PROVIDER_EMAIL,
            'email' => $email,
            'password' => 'secret123',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        return $user;
    }

    private function createUser(string $seed): User
    {
        $role = Role::ensureDefaultRole('student');

        return User::create([
            'user_id' => (string) \Illuminate\Support\Str::uuid(),
            'first_name' => 'Test',
            'last_name' => substr(md5($seed), 0, 8),
            'role_id' => $role->role_id,
        ]);
    }
}
