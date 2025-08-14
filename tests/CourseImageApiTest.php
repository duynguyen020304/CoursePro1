<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;



class CourseImageApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    private $baseUrl = 'http://localhost/api/course_image_api.php';

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
        $response = $this->http->request('GET', '', ['query' => ['courseID' => '123']]);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
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

        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $expiredToken]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Token đã hết hạn.', $body['message']);
    }

    public function testGetWithMissingCourseId()
    {
        $token = $this->generateToken();
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu thông tin', $body['message']);
    }

    public function testPostWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'courseID' => 'course123',
                'imageID' => 'img456'
            ]
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu thông tin', $body['message']);
    }

    public function testDeleteWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('DELETE', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'courseID' => 'course123'
            ]
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PUT', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);
        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Phương thức không được hỗ trợ', $body['message']);
    }
}