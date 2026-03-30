<?php
/**
 * Config class - Centralized configuration management
 * Loads environment variables from .env file
 * API_BASE_URL MUST be explicitly configured - no auto-detection
 */

class Config {
    private static $loaded = false;
    private static $variables = [];

    /**
     * Load .env file if not already loaded
     */
    private static function loadEnv(): void {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/../.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                // Parse KEY=VALUE
                if (strpos($line, '=') !== false) {
                    $parts = explode('=', $line, 2);
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    self::$variables[$key] = $value;
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable with fallback
     * @param string $key Variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed {
        self::loadEnv();

        if (isset(self::$variables[$key]) && self::$variables[$key] !== '') {
            return self::$variables[$key];
        }

        return $default;
    }

    /**
     * Get JWT Secret Key
     * @return string
     */
    public static function getJwtSecretKey(): string {
        return self::get('JWT_SECRET_KEY', '0196ce3e-ba28-7b47-8472-beded9ae0b5d');
    }

    /**
     * Get API Base URL - MUST be explicitly configured in .env
     * Throws exception if not configured
     * @return string
     * @throws RuntimeException if API_BASE_URL is not configured
     */
    public static function getApiBaseUrl(): string {
        $envUrl = self::get('API_BASE_URL', '');

        if ($envUrl === '') {
            throw new RuntimeException(
                'API_BASE_URL must be explicitly configured in .env file. ' .
                'Example: API_BASE_URL=http://localhost:8001/api/'
            );
        }

        return rtrim($envUrl, '/') . '/';
    }

    /**
     * Get Base URL (project root, derived from API_BASE_URL)
     * @return string
     */
    public static function getBaseUrl(): string {
        $apiUrl = self::getApiBaseUrl();
        // Remove /api suffix
        $baseUrl = preg_replace('/\/api\/?$/', '', $apiUrl);
        return rtrim($baseUrl, '/') . '/';
    }

    /**
     * Check if API_BASE_URL is configured
     * @return bool
     */
    public static function isApiBaseUrlConfigured(): bool {
        $envUrl = self::get('API_BASE_URL', '');
        return $envUrl !== '';
    }
}