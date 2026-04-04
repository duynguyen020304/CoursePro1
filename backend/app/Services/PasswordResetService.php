<?php

namespace App\Services;

use App\Models\UserAccount;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

class PasswordResetService
{
    private string $algorithm = 'HS256';

    /**
     * Generate a JWT password reset link for a user.
     * Token dies when password changes because signing key includes password hash.
     */
    public function generateJwtResetLink(UserAccount $userAccount, int $expiresInMinutes = 60): string
    {
        $now = time();
        $payload = [
            'sub' => $userAccount->user_id,
            'email' => $userAccount->email,
            'iat' => $now,
            'exp' => $now + ($expiresInMinutes * 60),
            'purpose' => 'password_reset',
        ];
        
        $signingKey = $this->getSigningKey($userAccount);
        return JWT::encode($payload, $signingKey, $this->algorithm);
    }

    /**
     * Validate a JWT password reset token.
     * Returns user_id if valid, null if invalid/expired/wrong signature.
     */
    public function validateJwtResetToken(string $jwt): ?string
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                return null;
            }
            $payload = json_decode(base64_decode($parts[1]), true);
            $userId = $payload['sub'] ?? null;
            
            if (!$userId) {
                return null;
            }
            
            $userAccount = UserAccount::find($userId);
            if (!$userAccount) {
                return null;
            }
            
            $signingKey = $this->getSigningKey($userAccount);
            $decoded = JWT::decode($jwt, new Key($signingKey, $this->algorithm));
            
            if (($decoded->purpose ?? '') !== 'password_reset') {
                return null;
            }
            
            if (!isset($decoded->exp) || $decoded->exp < time()) {
                return null;
            }
            
            return $decoded->sub;
        } catch (\Exception $e) {
            Log::channel('single')->debug('JWT validation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function getSigningKey(UserAccount $userAccount): string
    {
        $password = $userAccount->getAttributes()['password'] ?? '';
        return hash('sha256', $password . config('app.key'));
    }
}
