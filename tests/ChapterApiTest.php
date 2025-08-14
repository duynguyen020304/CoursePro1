<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;


class ChapterApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/api/chapter_api.php';
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';

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

    public function testGetChaptersWithoutCourseId()
    {
        $response = $this->http->request('GET');
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('data', $body);
    }

    public function testGetChaptersWithCourseId()
    {
        $response = $this->http->request('GET', '', ['query' => ['courseID' => 'course123']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('data', $body);
    }


    public function testDeleteChapterWithMissingId()
    {
        $response = $this->http->request('DELETE');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }

    public function testDeleteChapterWithId()
    {
        $response = $this->http->request('DELETE', '', ['query' => ['id' => 'chap456']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }

    public function testInvalidRequestMethod()
    {
        $response = $this->http->request('PATCH');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }
}