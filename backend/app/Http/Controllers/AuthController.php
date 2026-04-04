<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAccount;
use App\Models\Student;
use App\Models\PasswordResetToken;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $userAccount = UserAccount::findByEmail($request->email);

        if (!$userAccount || !Hash::check($request->password, $userAccount->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($userAccount->is_deleted) {
            throw ValidationException::withMessages([
                'email' => ['This account has been deactivated.'],
            ]);
        }

        $user = $userAccount->user;

        $authService = new AuthService();
        $accessToken = $authService->createAccessToken($userAccount);
        $refreshToken = $authService->createRefreshToken(
            $userAccount->user_id,
            $request->ip(),
            $request->userAgent()
        )['raw_token'];

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUserPayload($userAccount),
            ],
        ])
            ->withCookie($this->makeAccessTokenCookie($accessToken))
            ->withCookie($this->makeRefreshTokenCookie($refreshToken));
    }

    /**
     * Handle user signup
     */
    public function signup(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:user_accounts,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $userId = Str::uuid();

        // Create user
        $user = User::create([
            'user_id' => $userId,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'role_id' => 'student',
        ]);

        // Create auth account
        $userAccount = UserAccount::create([
            'user_id' => $userId,
            'provider' => 'email',
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_verified' => false,
        ]);

        // Create student record
        Student::create([
            'student_id' => Str::uuid(),
            'user_id' => $userId,
        ]);

        $authService = new AuthService();
        $accessToken = $authService->createAccessToken($userAccount);
        $refreshToken = $authService->createRefreshToken(
            $userAccount->user_id,
            $request->ip(),
            $request->userAgent()
        )['raw_token'];

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => [
                'user' => $this->formatUserPayload($userAccount),
            ],
        ], 201)
            ->withCookie($this->makeAccessTokenCookie($accessToken))
            ->withCookie($this->makeRefreshTokenCookie($refreshToken));
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:user_accounts,email',
        ]);

        $userAccount = UserAccount::findByEmail($request->email);

        if (!$userAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found',
            ], 404);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(60);

        PasswordResetToken::updateOrCreate(
            ['user_id' => $userAccount->user_id],
            ['token' => $code, 'expires_at' => $expiresAt]
        );

        try {
            Mail::to($userAccount->email)->send(new PasswordResetMail($code, $expiresAt));
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $userAccount->email,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info("[password_reset] forgot_password email={$userAccount->email} ip={$request->ip()} user_agent={$request->userAgent()} result=code_sent");

        return response()->json([
            'success' => true,
            'message' => 'Password reset code sent to your email',
        ]);
    }

    /**
     * Handle JWT-based password reset request
     */
    public function forgotPasswordJwt(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:user_accounts,email',
        ]);

        $userAccount = UserAccount::findByEmail($request->email);

        if (!$userAccount) {
            return response()->json([
                'success' => true,
                'message' => 'If that email exists, a reset link has been sent',
            ]);
        }

        $passwordResetService = new \App\Services\PasswordResetService();
        $jwt = $passwordResetService->generateJwtResetLink($userAccount, 60);
        $expiresAt = now()->addMinutes(60);

        try {
            Mail::to($userAccount->email)->send(new PasswordResetMail($jwt, $expiresAt));
        } catch (\Exception $e) {
            Log::error('Failed to send password reset JWT email', [
                'email' => $userAccount->email,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info("[password_reset] jwt_link_request email={$userAccount->email} ip={$request->ip()} result=email_sent");

        return response()->json([
            'success' => true,
            'message' => 'If that email exists, a reset link has been sent',
        ]);
    }

    /**
     * Verify reset code
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $userAccount = UserAccount::where('email', $request->email)->first();

        if (!$userAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found',
            ], 404);
        }

        $reset = PasswordResetToken::where('user_id', $userAccount->user_id)
            ->where('token', $request->code)
            ->first();

        if (!$reset) {
            Log::info("[password_reset] verify_code email={$request->email} ip={$request->ip()} user_agent={$request->userAgent()} result=failure_invalid_code");
            return response()->json([
                'success' => false,
                'message' => 'Invalid code',
            ], 400);
        }

        Log::info("[password_reset] verify_code email={$request->email} ip={$request->ip()} user_agent={$request->userAgent()} result=success");

        return response()->json([
            'success' => true,
            'message' => 'Code verified successfully',
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        $userAccount = UserAccount::where('email', $request->email)->first();

        if (!$userAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found',
            ], 404);
        }

        $token = $request->token;
        $isJwt = strlen($token) > 50 && str_contains($token, '.');

        if ($isJwt) {
            // JWT-based reset
            $passwordResetService = new \App\Services\PasswordResetService();
            $validUserId = $passwordResetService->validateJwtResetToken($token);

            if (!$validUserId || $validUserId !== $userAccount->user_id) {
                Log::info("[password_reset] reset_password_attempt email={$request->email} ip={$request->ip()} method=jwt result=failure_invalid_token");
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                ], 401);
            }

            // Invalidate all refresh tokens (security: prevent session hijacking)
            \App\Models\RefreshToken::revokeAllForUser($userAccount->user_id);

            // Delete any existing password reset tokens
            PasswordResetToken::where('user_id', $userAccount->user_id)->delete();

        } else {
            // 6-digit code reset
            $reset = PasswordResetToken::where('user_id', $userAccount->user_id)
                ->where('token', $token)
                ->first();

            if (!$reset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired code',
                ], 400);
            }

            // Check expiry
            if ($reset->expires_at && $reset->expires_at->isPast()) {
                Log::info("[password_reset] reset_password_attempt email={$request->email} ip={$request->ip()} method=code result=failure_code_expired");
                return response()->json([
                    'success' => false,
                    'message' => 'Code has expired',
                ], 400);
            }

            // Delete used reset token
            $reset->delete();

            // Invalidate all refresh tokens
            \App\Models\RefreshToken::revokeAllForUser($userAccount->user_id);
        }

        $userAccount->password = Hash::make($request->password);
        $userAccount->save();

        Log::info("[password_reset] password_changed email={$userAccount->email} method=" . ($isJwt ? 'jwt' : 'code'));

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        // The authenticated user is UserAccount via Sanctum
        $userAccount = $request->user();

        if (!Hash::check($request->current_password, $userAccount->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $userAccount->password = Hash::make($request->password);
        $userAccount->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Return the authenticated user payload.
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->formatUserPayload($request->user()),
            ],
        ]);
    }

    /**
     * Logout user - revoke the current access and refresh token pair.
     */
    public function logout(Request $request)
    {
        $currentAccessToken = $request->user()->currentAccessToken();
        if ($currentAccessToken) {
            $currentAccessToken->delete();
        }

        $authService = new AuthService();
        $refreshToken = $request->cookie('refresh_token');

        if (is_string($refreshToken) && $refreshToken !== '') {
            $authService->revokeRefreshToken($refreshToken);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ])
            ->withCookie($this->expireCookie(name: 'access_token', path: '/'))
            ->withCookie($this->expireCookie(name: 'refresh_token', path: '/api/auth'));
    }

    /**
     * Handle Google OAuth login
     */
    public function googleLogin(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'redirectUri' => 'required|string|url',
        ]);

        $authService = new AuthService();

        try {
            $result = $authService->handleGoogleOAuth(
                $request->code,
                $request->redirectUri,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => $result['is_new_user'] ? 'Account created successfully' : 'Login successful',
                'data' => [
                    'user' => $result['user'],
                    'is_new_user' => $result['is_new_user'],
                ],
            ])
                ->withCookie($this->makeAccessTokenCookie($result['access_token']))
                ->withCookie($this->makeRefreshTokenCookie($result['refresh_token']));

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'errors' => $e->errors(),
            ], 401);
        } catch (\Exception $e) {
            Log::error('Google OAuth error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during authentication',
            ], 500);
        }
    }

    /**
     * Refresh access token using refresh token
     */
    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token not provided',
            ], 401);
        }

        $authService = new AuthService();

        try {
            [$userAccount, $currentRefreshToken] = $authService->validateRefreshToken($refreshToken);

            $accessToken = $authService->createAccessToken($userAccount);

            $newRefreshToken = $authService->createRefreshToken(
                $userAccount->user_id,
                $request->ip(),
                $request->userAgent()
            )['raw_token'];

            $currentRefreshToken->revoke();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'user' => $this->formatUserPayload($userAccount),
                ],
            ])
                ->withCookie($this->makeAccessTokenCookie($accessToken))
                ->withCookie($this->makeRefreshTokenCookie($newRefreshToken));

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired refresh token',
            ], 401)
                ->withCookie($this->expireCookie(name: 'access_token', path: '/'))
                ->withCookie($this->expireCookie(name: 'refresh_token', path: '/api/auth'));
        }
    }

    private function formatUserPayload(\App\Models\UserAccount $userAccount): array
    {
        $user = $userAccount->user;

        return [
            'user_id' => $user->user_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $userAccount->email,
            'role_id' => $user->role_id,
            'profile_image' => $user->profile_image,
        ];
    }

    private function makeAccessTokenCookie(string $token): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            name: 'access_token',
            value: $token,
            minutes: 120,
            path: '/',
            domain: config('session.domain'),
            secure: (bool) config('session.secure'),
            httpOnly: true,
            raw: false,
            sameSite: config('session.same_site', 'lax'),
        );
    }

    private function makeRefreshTokenCookie(string $token): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            name: 'refresh_token',
            value: $token,
            minutes: 10080,
            path: '/api/auth',
            domain: config('session.domain'),
            secure: (bool) config('session.secure'),
            httpOnly: true,
            raw: false,
            sameSite: config('session.same_site', 'lax'),
        );
    }

    private function expireCookie(string $name, string $path): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            name: $name,
            value: '',
            minutes: -1,
            path: $path,
            domain: config('session.domain'),
            secure: (bool) config('session.secure'),
            httpOnly: true,
            raw: false,
            sameSite: config('session.same_site', 'lax'),
        );
    }
}
