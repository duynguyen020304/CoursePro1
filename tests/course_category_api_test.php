<?php

// tests/CourseCategoryApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('CourseCategoryService')) {
    class CourseCategoryService
    {
        public function get_courses_by_category_id($categoryID) {}
        public function get_categories_by_course_id($courseID) {}
        public function add_category_to_course($courseID, $categoryID) {}
        public function unlink_course_category($courseID, $categoryID) {}
    }
}

// Mock lớp ServiceResponse vì nó được sử dụng trong API
if (!class_exists('ServiceResponse')) {
    class ServiceResponse
    {
        public $success;
        public $message;
        public $data;

        public function __construct($success = false, $message = '', $data = null)
        {
            $this->success = $success;
            $this->message = $message;
            $this->data = $data;
        }
    }
}


class CourseCategoryApiTest extends TestCase
{
    private $http;
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/course_category_api.php';

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

    public function testGetWithMissingIds()
    {
        // Test trường hợp GET mà không cung cấp courseID hay categoryID
        $response = $this->http->request('GET');

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu courseID', $body['message']);
    }

    public function testGetByCourseId()
    {
        // Test trường hợp GET thành công với courseID.
        // Test này chỉ xác nhận API trả về 200 OK. Việc kiểm tra service có được gọi đúng không
        // đòi hỏi phải tái cấu trúc API để sử dụng Dependency Injection.
        $response = $this->http->request('GET', '', ['query' => ['courseID' => 'course123']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
    }

    public function testGetByCategoryId()
    {
        // Test trường hợp GET thành công với categoryID.
        $response = $this->http->request('GET', '', ['query' => ['categoryID' => 'cat456']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
    }

    public function testPostWithMissingData()
    {
        // Test trường hợp POST thiếu dữ liệu
        $response = $this->http->request('POST', '', [
            'json' => ['courseID' => 'course123'] // Thiếu categoryID
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu courseID hoặc categoryID', $body['message']);
    }

    public function testDeleteWithMissingData()
    {
        // Test trường hợp DELETE thiếu dữ liệu
        $response = $this->http->request('DELETE', '', [
            'json' => ['categoryID' => 'cat456'] // Thiếu courseID
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu courseID hoặc categoryID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        // Sử dụng phương thức PUT không được hỗ trợ
        $response = $this->http->request('PUT');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Phương thức không được hỗ trợ', $body['message']);
    }
}
