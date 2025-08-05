<?php

// tests/CategoryApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

// Mock các class phụ thuộc để môi trường test không bị lỗi
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
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/category_api.php';

    protected function setUp(): void
    {
        // Khởi tạo Guzzle Client để thực hiện các request HTTP
        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false, // Tắt việc Guzzle tự động ném exception cho response 4xx/5xx
        ]);
    }

    protected function tearDown(): void
    {
        $this->http = null;
    }

    // --- Bắt đầu các Test Case ---

    public function testGetCategoriesWithoutTreeParameter()
    {
        // Test này chỉ có thể xác nhận API trả về 200 OK.
        // Việc kiểm tra service->get_all_categories() có được gọi hay không
        // đòi hỏi phải tái cấu trúc API để sử dụng Dependency Injection.
        $response = $this->http->request('GET');
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertTrue($body['success']);
    }

    public function testGetCategoriesWithTreeParameter()
    {
        // Tương tự test trên, chỉ xác nhận API hoạt động đúng với tham số `tree=1`.
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
                'parent_id' => 1 // Thiếu 'name'
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
                'id' => 1 // Thiếu 'name'
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
                'some_other_key' => 'value' // Thiếu 'id'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu ID để xóa', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        // Sử dụng phương thức PATCH không được hỗ trợ
        $response = $this->http->request('PATCH');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Phương thức không hỗ trợ', $body['message']);
    }
}
