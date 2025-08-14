<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

if (!class_exists('CategoryService')) {
    class CategoryService
    {
        public function get_nested_categories() {}
        public function get_all_categories() {}
        public function create($dto) {}
        public function update($dto) {}
        public function delete($id) {}
    }
}
if (!class_exists('CategoryDTO')) {
    class CategoryDTO
    {
        public function __construct($id, $name, $parent_id, $sort_order) {}
    }
}

class CategoryApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/path/to/your/api/category_api.php';

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