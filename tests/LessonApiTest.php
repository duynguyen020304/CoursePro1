<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;


class LessonApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    private $baseUrl = 'http://localhost/api/lesson_api.php';

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

    public function testGetWithMissingIds()
    {
        $token = $this->generateToken();
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu lessonID hoặc chapterID', $body['message']);
    }

    public function testPostWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'courseID' => 'course123',
                'chapterID' => 'chap456'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu bắt buộc', $body['message']);
    }

    public function testPutWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PUT', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'lessonID' => 'lesson789',
                'courseID' => 'course123'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu cần cập nhật', $body['message']);
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
        $this->assertEquals('Thiếu lessonID', $body['message']);
    }

}