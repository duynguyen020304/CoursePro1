<?php
/**
 * AuthHelper class - Centralized JWT authentication helper
 * Validates JWT tokens and provides decoded user data
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthHelper {
    /**
     * Validate JWT token from Authorization header
     * @return array Decoded user data or error response
     */
    public static function validateToken(): array {
        $authHeader = self::getAuthorizationHeader();

        if (!$authHeader) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy token xác thực.',
                'status_code' => 401
            ];
        }

        $token = self::extractBearerToken($authHeader);

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Token không hợp lệ. Vui lòng sử dụng Bearer token.',
                'status_code' => 401
            ];
        }

        try {
            $secretKey = Config::getJwtSecretKey();
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            return [
                'success' => true,
                'data' => $decoded->data,
                'status_code' => 200
            ];
        } catch (ExpiredException $e) {
            return [
                'success' => false,
                'message' => 'Token đã hết hạn.',
                'status_code' => 401
            ];
        } catch (SignatureInvalidException $e) {
            return [
                'success' => false,
                'message' => 'Chữ ký token không hợp lệ.',
                'status_code' => 401
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Token không hợp lệ hoặc có lỗi xảy ra: ' . $e->getMessage(),
                'status_code' => 401
            ];
        }
    }

    /**
     * Validate token and send HTTP response on failure
     * Use this in API endpoints to require authentication
     * @return object|null Decoded user data or null (sends HTTP response on failure)
     */
    public static function requireAuth(): ?object {
        $result = self::validateToken();

        if (!$result['success']) {
            http_response_code($result['status_code']);
            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
            exit;
        }

        return $result['data'];
    }

    /**
     * Get Authorization header from request
     * @return string|null
     */
    private static function getAuthorizationHeader(): ?string {
        $headers = null;

        // Try apache_request_headers() first
        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = $requestHeaders['Authorization'];
            }
        }

        // Fallback to $_SERVER
        if (empty($headers)) {
            $headers = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        }

        // Another fallback for nginx/fastcgi
        if (empty($headers)) {
            $headers = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
        }

        return $headers;
    }

    /**
     * Extract Bearer token from Authorization header
     * @param string $authHeader Authorization header value
     * @return string|null
     */
    private static function extractBearerToken(string $authHeader): ?string {
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Generate JWT token for user
     * @param object $userData User data to encode
     * @param int $expireHours Hours until expiration (default: 24)
     * @return string JWT token
     */
    public static function generateToken(object $userData, int $expireHours = 24): string {
        $secretKey = Config::getJwtSecretKey();
        $issuedAt = time();
        $expire = $issuedAt + (60 * 60 * $expireHours);
        $serverName = "CoursePro1";

        $tokenPayload = [
            'iss' => $serverName,
            'aud' => $serverName,
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => $expire,
            'data' => [
                'userID' => $userData->userID,
                'email' => $userData->email,
                'roleID' => $userData->roleID,
                'firstName' => $userData->firstName,
                'lastName' => $userData->lastName
            ]
        ];

        return JWT::encode($tokenPayload, $secretKey, 'HS256');
    }

    /**
     * Check if user has specific role
     * @param object $userData Decoded user data from token
     * @param int|array $requiredRoles Required role ID(s)
     * @return bool
     */
    public static function hasRole(object $userData, int|array $requiredRoles): bool {
        $userRoleID = $userData->roleID ?? 0;

        if (is_array($requiredRoles)) {
            return in_array($userRoleID, $requiredRoles);
        }

        return $userRoleID === $requiredRoles;
    }

    /**
     * Require user to have specific role (sends HTTP response on failure)
     * @param object $userData Decoded user data from token
     * @param int|array $requiredRoles Required role ID(s)
     * @return bool True if user has required role
     */
    public static function requireRole(object $userData, int|array $requiredRoles): bool {
        if (!self::hasRole($userData, $requiredRoles)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này.'
            ]);
            exit;
        }
        return true;
    }
}