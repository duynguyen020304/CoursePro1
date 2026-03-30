<?php
/**
 * Api class - Centralized API client supporting public and private endpoints
 * Public API: No authorization required
 * Private API: JWT Bearer token authorization
 */

require_once __DIR__ . '/../Model/Config/config.php';

class Api {
    private string $baseUrl;
    private ?string $token;

    /**
     * Constructor
     * @param string|null $baseUrl Optional custom base URL (defaults to Config::getApiBaseUrl())
     * @param string|null $token Optional JWT token for private API calls
     */
    public function __construct(?string $baseUrl = null, ?string $token = null) {
        $this->baseUrl = $baseUrl ?? Config::getApiBaseUrl();
        $this->token = $token;
    }

    /**
     * Set JWT token for private API calls
     * @param string $token JWT token
     */
    public function setToken(string $token): void {
        $this->token = $token;
    }

    /**
     * Clear the JWT token
     */
    public function clearToken(): void {
        $this->token = null;
    }

    /**
     * Get current token
     * @return string|null
     */
    public function getToken(): ?string {
        return $this->token;
    }

    /**
     * Build full URL from endpoint
     * @param string $endpoint API endpoint (e.g., 'user_api.php')
     * @param array $params Query parameters
     * @return string
     */
    private function buildUrl(string $endpoint, array $params = []): string {
        $url = $this->baseUrl . ltrim($endpoint, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Make HTTP request
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @param array $params Query parameters
     * @param bool $requireAuth Whether to include Authorization header
     * @return array Response data
     */
    private function request(string $method, string $endpoint, array $data = [], array $params = [], bool $requireAuth = false): array {
        $url = $this->buildUrl($endpoint, $params);

        $options = [
            'http' => [
                'method' => $method,
                'header' => "Content-Type: application/json\r\n",
                'ignore_errors' => true
            ]
        ];

        // Add Authorization header for private API calls
        if ($requireAuth && $this->token) {
            $options['http']['header'] .= "Authorization: Bearer " . $this->token . "\r\n";
        }

        // Add body for POST, PUT, DELETE
        if (in_array($method, ['POST', 'PUT', 'DELETE']) && !empty($data)) {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        // Get HTTP status code from response headers
        $statusLine = $http_response_header[0] ?? '';
        preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $statusLine, $matches);
        $statusCode = (int)($matches[1] ?? 500);

        return [
            'status_code' => $statusCode,
            'data' => json_decode($response, true) ?? [],
            'raw' => $response
        ];
    }

    // ==================== PUBLIC API METHODS ====================

    /**
     * Public GET request (no authorization)
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @return array
     */
    public function getPublic(string $endpoint, array $params = []): array {
        return $this->request('GET', $endpoint, [], $params, false);
    }

    /**
     * Public POST request (no authorization)
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @return array
     */
    public function postPublic(string $endpoint, array $data = []): array {
        return $this->request('POST', $endpoint, $data, [], false);
    }

    /**
     * Public PUT request (no authorization)
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @return array
     */
    public function putPublic(string $endpoint, array $data = []): array {
        return $this->request('PUT', $endpoint, $data, [], false);
    }

    /**
     * Public DELETE request (no authorization)
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @return array
     */
    public function deletePublic(string $endpoint, array $data = []): array {
        return $this->request('DELETE', $endpoint, $data, [], false);
    }

    // ==================== PRIVATE API METHODS ====================

    /**
     * Private GET request (with JWT authorization)
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @param string|null $token Override token (optional)
     * @return array
     */
    public function getPrivate(string $endpoint, array $params = [], ?string $token = null): array {
        $originalToken = $this->token;
        if ($token !== null) {
            $this->token = $token;
        }
        $result = $this->request('GET', $endpoint, [], $params, true);
        $this->token = $originalToken;
        return $result;
    }

    /**
     * Private POST request (with JWT authorization)
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @param string|null $token Override token (optional)
     * @return array
     */
    public function postPrivate(string $endpoint, array $data = [], ?string $token = null): array {
        $originalToken = $this->token;
        if ($token !== null) {
            $this->token = $token;
        }
        $result = $this->request('POST', $endpoint, $data, [], true);
        $this->token = $originalToken;
        return $result;
    }

    /**
     * Private PUT request (with JWT authorization)
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @param string|null $token Override token (optional)
     * @return array
     */
    public function putPrivate(string $endpoint, array $data = [], ?string $token = null): array {
        $originalToken = $this->token;
        if ($token !== null) {
            $this->token = $token;
        }
        $result = $this->request('PUT', $endpoint, $data, [], true);
        $this->token = $originalToken;
        return $result;
    }

    /**
     * Private DELETE request (with JWT authorization)
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @param string|null $token Override token (optional)
     * @return array
     */
    public function deletePrivate(string $endpoint, array $data = [], ?string $token = null): array {
        $originalToken = $this->token;
        if ($token !== null) {
            $this->token = $token;
        }
        $result = $this->request('DELETE', $endpoint, $data, [], true);
        $this->token = $originalToken;
        return $result;
    }

    // ==================== CONVENIENCE METHODS ====================

    /**
     * Check if response was successful (HTTP 200-299)
     * @param array $response Response array
     * @return bool
     */
    public static function isSuccess(array $response): bool {
        $statusCode = $response['status_code'] ?? 500;
        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Get response data array
     * @param array $response Response array
     * @return array
     */
    public static function getData(array $response): array {
        return $response['data'] ?? [];
    }

    /**
     * Get base URL being used
     * @return string
     */
    public function getBaseUrl(): string {
        return $this->baseUrl;
    }
}