<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

class CartItemApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    private $baseUrl = 'http://localhost/api/cart_item_api.php';

    protected function setUp(): void
    {
        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false
        ]);
    }

    protected function tearDown(): void
    {
        $this->http = null;
    }

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

    public function testPostCreateItemWithInvalidData()
    {
        $token = $this->generateToken(1, time() + 3600);
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'cartID' => 'cart123',
                'courseID' => 'course456',
                'quantity' => 0
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('error', $body['status']);
        $this->assertEquals('Invalid input data', $body['message']);
    }
    
    public function testGetItemsByCartWithoutCartId()
    {
        $token = $this->generateToken(1, time() + 3600);
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
            'json' => []
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('error', $body['status']);
        $this->assertEquals('Missing cartItemID or cartID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken(1, time() + 3600);
        $response = $this->http->request('PUT', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('error', $body['status']);
        $this->assertEquals('Invalid request method', $body['message']);
    }
}