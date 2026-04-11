<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAccount;
use App\Models\RefreshToken;
use App\Models\Role;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthService
{
    /**
     * Google OAuth configuration
     */
    private string $googleClientId;
    private string $googleClientSecret;
    private string $googleTokenUrl = 'https://oauth2.googleapis.com/token';
    private string $googleUserInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

    public function __construct()
    {
        $this->googleClientId = config('services.google.client_id');
        $this->googleClientSecret = config('services.google.client_secret');
    }

    /**
     * Exchange authorization code with Google for access token
     */
    public function exchangeGoogleCode(string $code, string $redirectUri): array
    {
        $response = Http::post($this->googleTokenUrl, [
            'code' => $code,
            'client_id' => $this->googleClientId,
            'client_secret' => $this->googleClientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            Log::error('Google token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new HttpException(400, 'Failed to authenticate with Google.');
        }

        return $response->json();
    }

    /**
     * Fetch user info from Google using access token
     */
    public function fetchGoogleUserInfo(string $accessToken): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($this->googleUserInfoUrl);

        if (!$response->successful()) {
            Log::error('Google userinfo fetch failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new HttpException(400, 'Failed to retrieve user information from Google.');
        }

        return $response->json();
    }

    /**
     * Find or create user from Google OAuth data
     * Implements anti-account-takeover logic
     */
    public function findOrCreateGoogleUser(array $googleUser): array
    {
        $googleSub = $googleUser['id'] ?? $googleUser['sub'] ?? null;
        $email = $googleUser['email'] ?? null;
        $emailVerified = $googleUser['verified_email'] ?? false;
        $name = $googleUser['name'] ?? '';
        $picture = $googleUser['picture'] ?? null;

        if (!$googleSub) {
            throw new HttpException(400, 'Google account identifier is missing.');
        }

        // Parse name into first/last
        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        // Step A: Look up existing Google-linked account (anti-takeover)
        $existingAccount = UserAccount::where('provider', 'google')
            ->where('provider_account_id', $googleSub)
            ->whereNull('deleted_at')
            ->first();

        if ($existingAccount) {
            $user = $existingAccount->user;
            $this->assertAccountCanAuthenticate($user, $existingAccount);

            if ($picture && !$user->profile_image) {
                $user->forceFill(['profile_image' => $picture])->save();
            }

            Log::info('Google OAuth: Found existing linked account', [
                'user_id' => $user->user_id,
                'google_sub' => $googleSub,
            ]);
            return [$user, $existingAccount, false]; // false = not new user
        }

        if (!$emailVerified) {
            throw new HttpException(400, 'Email not verified by Google.');
        }

        if ($email) {
            $emailAccount = UserAccount::findByEmail($email);

            if ($emailAccount) {
                $user = $emailAccount->user;
                $this->assertAccountCanAuthenticate($user, $emailAccount);

                $googleAccount = UserAccount::create([
                    'user_id' => $emailAccount->user_id,
                    'provider' => UserAccount::PROVIDER_GOOGLE,
                    'provider_account_id' => $googleSub,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => null,
                    'is_verified' => true,
                ]);

                if ($picture && !$user->profile_image) {
                    $user->forceFill(['profile_image' => $picture])->save();
                }

                Log::info('Google OAuth: Linked Google to existing email account', [
                    'user_id' => $user->user_id,
                    'email' => $email,
                ]);
                return [$user, $googleAccount, false]; // false = not new user
            }
        }

        // Step C: Create new user
        [$user, $userAccount] = DB::transaction(function () use ($email, $emailVerified, $firstName, $googleSub, $lastName, $picture) {
            $userId = Str::uuid();

            $role = Role::ensureDefaultRole('student');

            $user = User::create([
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role_id' => $role->role_id,
                'profile_image' => $picture,
            ]);

            $userAccount = UserAccount::create([
                'user_id' => $userId,
                'provider' => UserAccount::PROVIDER_GOOGLE,
                'provider_account_id' => $googleSub,
                'email' => $email,
                'email_verified_at' => $emailVerified ? now() : null,
                'password' => null,
                'is_verified' => $emailVerified,
            ]);

            Student::create([
                'student_id' => Str::uuid(),
                'user_id' => $userId,
            ]);

            return [$user, $userAccount];
        });

        Log::info('Google OAuth: Created new user', [
            'user_id' => $user->user_id,
            'email' => $email,
        ]);

        return [$user, $userAccount, true]; // true = new user
    }

    /**
     * Create refresh token for user
     * Token is HMAC-SHA256 hashed before storage
     */
    public function createRefreshToken(string $userId, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        // Generate 64-byte random token
        $rawToken = bin2hex(random_bytes(64));

        // Hash with HMAC-SHA256 using app key
        $hashedToken = hash_hmac('sha256', $rawToken, config('app.key'));

        $refreshToken = RefreshToken::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'token' => $hashedToken,
            'expires_at' => now()->addDays(7),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        return [
            'raw_token' => $rawToken, // Return raw token to client (never stored)
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * Validate and use refresh token
     * Returns user if valid, throws otherwise
     */
    public function validateRefreshToken(string $rawToken): array
    {
        $hashedToken = hash_hmac('sha256', $rawToken, config('app.key'));

        $refreshToken = RefreshToken::findValidByToken($hashedToken);

        if (!$refreshToken) {
            throw new HttpException(401, 'Invalid or expired refresh token.');
        }

        $userAccount = UserAccount::findPreferredForUser($refreshToken->user_id);

        if (!$userAccount) {
            throw new HttpException(401, 'User account not found.');
        }

        $this->assertAccountCanAuthenticate($userAccount->user, $userAccount);

        return [$userAccount, $refreshToken];
    }

    /**
     * Revoke all refresh tokens for a user (logout, password reset, etc.)
     */
    public function revokeAllRefreshTokens(string $userId): int
    {
        return RefreshToken::revokeAllForUser($userId);
    }

    /**
     * Revoke a single refresh token by its raw token value.
     */
    public function revokeRefreshToken(string $rawToken): bool
    {
        $hashedToken = hash_hmac('sha256', $rawToken, config('app.key'));

        $refreshToken = RefreshToken::where('token', $hashedToken)
            ->where('is_deleted', false)
            ->first();

        if (!$refreshToken) {
            return false;
        }

        $refreshToken->revoke();

        return true;
    }

    /**
     * Generate access token for user account
     */
    public function createAccessToken(UserAccount $userAccount): string
    {
        return $userAccount->createToken('auth-token')->plainTextToken;
    }

    /**
     * Full Google OAuth flow - returns user data with tokens
     */
    public function handleGoogleOAuth(string $code, string $redirectUri, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        // 1. Exchange code for tokens
        $tokenData = $this->exchangeGoogleCode($code, $redirectUri);
        $accessToken = $tokenData['access_token'];

        // 2. Fetch user info
        $googleUser = $this->fetchGoogleUserInfo($accessToken);

        // 3. Find or create user
        [$user, $userAccount, $isNewUser] = $this->findOrCreateGoogleUser($googleUser);

        // 4. Create access token
        $accessToken = $this->createAccessToken($userAccount);

        // 5. Create refresh token
        $tokenResult = $this->createRefreshToken(
            $userAccount->user_id,
            $ipAddress,
            $userAgent
        );
        $rawRefreshToken = $tokenResult['raw_token'];

        return [
            'user' => [
                'user_id' => $user->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $userAccount->email,
                'role_id' => $user->role_id,
                'role_code' => $user->role?->role_code,
                'profile_image' => $user->profile_image,
            ],
            'access_token' => $accessToken,
            'refresh_token' => $rawRefreshToken,
            'is_new_user' => $isNewUser,
        ];
    }

    private function assertAccountCanAuthenticate(?User $user, UserAccount $account): void
    {
        if (!$user || $user->trashed() || $user->is_deleted) {
            throw new HttpException(401, 'This account has been deleted.');
        }

        if ($account->trashed() || $account->is_deleted) {
            throw new HttpException(401, 'This account has been deleted.');
        }

        if (!$user->is_active || !$account->is_active) {
            throw new HttpException(401, 'This account has been deactivated.');
        }
    }
}
