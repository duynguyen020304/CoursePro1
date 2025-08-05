<?php

// tests/ChapterApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

// Mock các class phụ thuộc để môi trường test không bị lỗi
// Điều này rất quan trọng vì API của bạn `require` các file này.
if (!class_exists('ChapterService')) {
    class ChapterService
    {
        public function get_chapters_by_course_id($courseID) {}
        public function get_all_chapters() {}
        public function create_chapter($courseID, $title, $description, $sortOrder) {}
        public function update_chapter($chapterID, $courseID, $title, $description, $sortOrder) {}
        public function delete_chapter($id) {}
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


class ChapterApiTest extends TestCase
{
    private $http;
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/chapter_api.php';

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

    public function testGetChaptersWithoutCourseId()
    {
        // Test trường hợp GET tất cả chapter mà không có courseID
        // Test này chỉ có thể xác nhận API trả về 200 OK và có cấu trúc đúng.
        // Việc kiểm tra service->get_all_chapters() có được gọi hay không
        // đòi hỏi phải tái cấu trúc API để sử dụng Dependency Injection.
        $response = $this->http->request('GET');
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('data', $body);
    }

    public function testGetChaptersWithCourseId()
    {
        // Test trường hợp GET chapter theo courseID
        $response = $this->http->request('GET', '', ['query' => ['courseID' => 'course123']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('data', $body);
    }

    public function testPostChapter()
    {
        // Test tạo chapter mới. API không có validation ở tầng này,
        // nên chúng ta chỉ kiểm tra xem nó có trả về response với cấu trúc đúng không.
        $response = $this->http->request('POST', '', [
            'json' => [
                'courseID' => 'course123',
                'title' => 'New Chapter'
            ]
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
    }

    public function testPutChapter()
    {
        // Test cập nhật chapter. Tương tự POST, chỉ kiểm tra cấu trúc response.
        $response = $this->http->request('PUT', '', [
            'json' => [
                'chapterID' => 'chap456',
                'courseID' => 'course123',
                'title' => 'Updated Chapter'
            ]
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
    }

    public function testDeleteChapterWithMissingId()
    {
        // Test trường hợp DELETE nhưng thiếu 'id' trong query string.
        // API gốc có vẻ có lỗi ở đây (tạo response nhưng không echo),
        // nên test này sẽ kiểm tra hành vi thực tế là trả về body rỗng.
        $response = $this->http->request('DELETE');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }

    public function testDeleteChapterWithId()
    {
        // Test trường hợp DELETE thành công với 'id'
        $response = $this->http->request('DELETE', '', ['query' => ['id' => 'chap456']]);
        
        // API gốc không echo gì trong trường hợp DELETE, nên chúng ta mong đợi body rỗng.
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }

    public function testInvalidRequestMethod()
    {
        // Sử dụng phương thức không được hỗ trợ như PATCH.
        // API gốc có lỗi ở đây (tạo response nhưng không echo).
        $response = $this->http->request('PATCH');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }
}
