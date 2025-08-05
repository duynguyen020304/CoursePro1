<?php

// tests/CartItemApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

// Mock the dependent classes so the test environment doesn't crash
// when the API script tries to include them.
if (!class_exists('CartItemBLL')) {
    class CartItemBLL
    {
        public function create_item($dto) {}
        public function get_items_by_cart($cartID) {}
        public function delete_item($cartItemID) {}
        public function clear_cart($cartID) {}
    }
}
if (!class_exists('CartItemDTO')) {
    class CartItemDTO
    {
        public function __construct($cartItemID, $cartID, $courseID, $quantity) {}
    }
}

class CartItemApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    // IMPORTANT: Change this to your actual local URL for cart_item_api.php
    private $baseUrl = 'http://localhost/path/to/your/api/cart_item_api.php'; 

    protected function setUp(): void
    {
        // Setup Guzzle client to make HTTP requests to the API
        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false // Disable Guzzle exceptions on 4xx/5xx responses
        ]);
    }

    protected function tearDown(): void
    {
        $this->http = null;
    }

    /**
     * Generates a JWT token for testing purposes.
     */
    private function generateToken(int $userID, int $expirationTime): string
    {
        $payload = [
            'iss' => 'your_issuer',
            'aud' => 'your_audience',
            'iat' => time(),
            'exp' => $expirationTime,
            'data' => ['userID' => $userID]
        ];
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    // --- Authentication Tests ---

    public function testShouldReturn401WhenNoTokenIsProvided()
    {
        $response = $this->http->request('GET');
        $this->assertEquals(401, $response->getStatusCode());
        $responseBody = json_decode($response->getBody(), true);
        $this->assertEquals('Không tìm thấy token xác thực.', $responseBody['message']);
    }

    public function testShouldReturn401ForExpiredToken()
    {
        $expiredToken = $this->generateToken(1, time() - 3600);
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $expiredToken]
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $responseBody = json_decode($response->getBody(), true);
        $this->assertEquals('Token đã hết hạn.', $responseBody['message']);
    }

    public function testShouldReturn401ForInvalidSignature()
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => ['userID' => 1]
        ];
        $invalidToken = JWT::encode($payload, 'wrong-secret-key', 'HS256');
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $invalidToken]
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $responseBody = json_decode($response->getBody(), true);
        $this->assertEquals('Chữ ký token không hợp lệ.', $responseBody['message']);
    }

    // --- Endpoint Logic Tests ---

    public function testPostCreateItemWithInvalidData()
    {
        $token = $this->generateToken(1, time() + 3600);
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'cartID' => 'cart123',
                'courseID' => 'course456',
                'quantity' => 0 // Invalid quantity
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode()); // API returns 200 even for errors
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('error', $body['status']);
        $this->assertEquals('Invalid input data', $body['message']);
    }
    
    public function testGetItemsByCartWithoutCartId()
    {
        $token = $this->generateToken(1, time() + 3600);
        // Request without cartID query parameter
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('error', $body['status']);
        $this->assertEquals('Cart ID is required', $body['message']);
    }

    public function testDeleteWithMissingIdentifier()
    {
        $token = $this->generateToken(1, time() + 3600);
        $response = $this->http->request('DELETE', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [] // Empty body
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('error', $body['status']);
        $this->assertEquals('Missing cartItemID or cartID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken(1, time() + 3600);
        $response = $this->http->request('PUT', '', [ // PUT is not supported
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('error', $body['status']);
        $this->assertEquals('Invalid request method', $body['message']);
    }
    
    /**
     * NOTE: Success cases (e.g., successfully creating an item) are harder to test
     * in this black-box setup because they depend on the CartItemBLL.
     * To properly test them, you would need to refactor the API to use
     * dependency injection, allowing you to inject a mock BLL object.
     * The tests here focus on input validation and routing logic within the API file itself.
     */
}
