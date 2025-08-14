<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Firebase\JWT\JWT;



class CategoryApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/api/category_api.php';
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

    public function testGetCategoriesWithoutTreeParameter()
    {
        $response = $this->http->request('GET');
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertTrue($body['success']);
    }

    public function testGetCategoriesWithTreeParameter()
    {
        $response = $this->http->request('GET', '', ['query' => ['tree' => '1']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertTrue($body['success']);
    }

    public function testPostCategoryWithMissingName()
    {
        $response = $this->http->request('POST', '', [
            'json' => [
                'parent_id' => 1
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu tên danh mục', $body['message']);
    }

    public function testPutCategoryWithMissingData()
    {
        $response = $this->http->request('PUT', '', [
            'json' => [
                'id' => 1
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu dữ liệu cập nhật', $body['message']);
    }

    public function testDeleteCategoryWithMissingId()
    {
        $response = $this->http->request('DELETE', '', [
            'json' => [
                'some_other_key' => 'value'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu ID để xóa', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $response = $this->http->request('PATCH');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Phương thức không hỗ trợ', $body['message']);
    }
}