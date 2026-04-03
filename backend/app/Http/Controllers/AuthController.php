<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAccount;
use App\Models\Student;
use App\Models\PasswordResetToken;
use App\Models\RefreshToken;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        // Load the user profile
        $user = $userAccount->user;

        $token = $userAccount->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'user_id' => $user->user_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $userAccount->email,
                    'role_id' => $user->role_id,
                    'profile_image' => $user->profile_image,
                ],
                'token' => $token,
            ],
        ]);
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

        // Create user profile
        $user = User::create([
            'user_id' => $userId,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'role_id' => 'student',
        ]);

        // Create user account (authentication)
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

        $token = $userAccount->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => [
                'user' => [
                    'user_id' => $user->user_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $userAccount->email,
                    'role_id' => $user->role_id,
                ],
                'token' => $token,
            ],
        ], 201);
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

        PasswordResetToken::updateOrCreate(
            ['user_id' => $userAccount->user_id],
            ['token' => $code]
        );

        // TODO: Send email with code
        Log::info("Password reset code for {$userAccount->email}: {$code}");

        return response()->json([
            'success' => true,
            'message' => 'Password reset code sent to your email',
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
            return response()->json([
                'success' => false,
                'message' => 'Invalid code',
            ], 400);
        }

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
            'code' => 'required|string|size:6',
            'password' => 'required|min:6|confirmed',
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
            return response()->json([
                'success' => false,
                'message' => 'Invalid code',
            ], 400);
        }

        $userAccount->password = Hash::make($request->password);
        $userAccount->save();

        // Delete used reset token
        $reset->delete();

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
     * Logout user - revoke all tokens
     */
    public function logout(Request $request)
    {
        // Revoke access token
        $request->user()->currentAccessToken()->delete();

        // Revoke all refresh tokens for this user
        $authService = new AuthService();
        $authService->revokeAllRefreshTokens($request->user()->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
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

            // Set cookies for tokens
            $cookieConfig = [
                'secure' => config('app.env') === 'production',
                'same_site' => 'lax',
            ];

            $accessTokenCookie = cookie(
                'access_token',
                $result['access_token'],
                60, // 60 minutes
                '/',
                null,
                $cookieConfig['secure'],
                true, // HttpOnly
                $cookieConfig['same_site']
            );

            $refreshTokenCookie = cookie(
                'refresh_token',
                $result['refresh_token'],
                30 * 24 * 60, // 30 days in minutes
                '/api/auth',
                null,
                $cookieConfig['secure'],
                true, // HttpOnly
                $cookieConfig['same_site']
            );

            return response()->json([
                'success' => true,
                'message' => $result['is_new_user'] ? 'Account created successfully' : 'Login successful',
                'data' => [
                    'user' => $result['user'],
                    'token' => $result['access_token'],
                    'is_new_user' => $result['is_new_user'],
                ],
            ])->withCookie($accessTokenCookie)->withCookie($refreshTokenCookie);

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
            $userAccount = $authService->validateRefreshToken($refreshToken);

            // Create new access token
            $accessToken = $authService->createAccessToken($userAccount);

            // Create new refresh token (rotation)
            [$newRefreshToken, $refreshTokenModel] = $authService->createRefreshToken(
                $userAccount->user_id,
                $request->ip(),
                $request->userAgent()
            );

            $cookieConfig = [
                'secure' => config('app.env') === 'production',
                'same_site' => 'lax',
            ];

            $accessTokenCookie = cookie(
                'access_token',
                $accessToken,
                60,
                '/',
                null,
                $cookieConfig['secure'],
                true,
                $cookieConfig['same_site']
            );

            $refreshTokenCookie = cookie(
                'refresh_token',
                $newRefreshToken,
                30 * 24 * 60,
                '/api/auth',
                null,
                $cookieConfig['secure'],
                true,
                $cookieConfig['same_site']
            );

            $user = $userAccount->user;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'user' => [
                        'user_id' => $user->user_id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $userAccount->email,
                        'role_id' => $user->role_id,
                        'profile_image' => $user->profile_image,
                    ],
                ],
            ])->withCookie($accessTokenCookie)->withCookie($refreshTokenCookie);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired refresh token',
            ], 401);
        }
    }
}