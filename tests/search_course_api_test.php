<?php

// tests/SearchCourseApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('CourseService')) {
    class CourseService
    {
        public function search_courses_by_title_for_course_management($title) {}
        public function search_courses_by_title($title) {}
        public function get_all_courses() {}
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


class SearchCourseApiTest extends TestCase
{
    private $http;
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/search_course_api.php';

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

    public function testSearchForCourseManagement()
    {
        // Test trường hợp tìm kiếm cho trang quản lý khóa học.
        // Test này chỉ xác nhận API trả về 200 OK và có cấu trúc đúng.
        $response = $this->http->request('GET', '', [
            'query' => [
                'isGetForCourseManagement' => 'true',
                'title' => 'PHP'
            ]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('data', $body);
    }

    public function testSearchByTitleOnly()
    {
        // Test trường hợp tìm kiếm chỉ bằng title.
        $response = $this->http->request('GET', '', [
            'query' => ['title' => 'Java']
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        // API này trả về trực tiếp mảng data, không có key 'success' hay 'message'
        $body = json_decode($response->getBody(), true);
        $this->assertIsArray($body);
    }

    public function testSearchWithoutTitle()
    {
        // Test trường hợp không có title, sẽ gọi get_all_courses.
        $response = $this->http->request('GET');
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertIsArray($body);
    }

    public function testInvalidRequestMethod()
    {
        // Sử dụng phương thức POST không được hỗ trợ
        $response = $this->http->request('POST');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Phương thức không được hỗ trợ', $body['message']);
    }
}
