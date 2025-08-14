<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;


class ReviewApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    private $baseUrl = 'http://localhost/path/to/your/api/review_api.php';

    protected function setUp(): void
    {
        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false,
        ]);
    }

    protected function tearDown(): void
    {
        $this->http = null;
    }

    private function generateToken(int $userID = 1): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => ['userID' => $userID]
        ];
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function testShouldReturn401WhenNoTokenIsProvided()
    {
        $response = $this->http->request('GET');
        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Không tìm thấy token xác thực.', $body['message']);
    }

    public function testShouldReturn401ForExpiredToken()
    {
        $payload = [
            'iat' => time() - 3601,
            'exp' => time() - 3600,
            'data' => ['userID' => 1]
        ];
        $expiredToken = JWT::encode($payload, $this->secretKey, 'HS256');

        $response = $this->http->request('POST', [
            'headers' => ['Authorization' => 'Bearer ' . $expiredToken]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Token đã hết hạn.', $body['message']);
    }

    public function testGetWithoutIdShouldGetAllResources()
    {
        $token = $this->generateToken();
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'reviewID' => 'rev123',
                'userID' => 'user456',
                'courseID' => 'course789'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu', $body['message']);
    }

    public function testPutWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PUT', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'reviewID' => 'rev123',
                'userID' => 'user456'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu', $body['message']);
    }

    public function testDeleteWithMissingId()
    {
        $token = $this->generateToken();
        $response = $this->http->request('DELETE', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => []
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu reviewID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PATCH', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Phương thức không hỗ trợ', $body['message']);
    }
}