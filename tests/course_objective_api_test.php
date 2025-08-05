<?php

// tests/CourseObjectiveApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('CourseObjectiveService')) {
    class CourseObjectiveService
    {
        public function get_objective_by_objective_id($objectiveID) {}
        public function get_objectives_by_course_id($courseID) {}
        public function create($courseID, $objective) {}
        public function update($objectiveID, $courseID, $objective) {}
        public function delete($objectiveID) {}
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


class CourseObjectiveApiTest extends TestCase
{
    private $http;
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/course_objective_api.php';

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
        // Test trường hợp GET mà không cung cấp objectiveID hay courseID
        $response = $this->http->request('GET');

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu objectiveID hoặc courseID', $body['message']);
    }

    public function testGetByCourseId()
    {
        // Test trường hợp GET thành công với courseID.
        // Test này chỉ xác nhận API trả về mã trạng thái thành công.
        $response = $this->http->request('GET', '', ['query' => ['courseID' => 'course123']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
    }

    public function testPostWithMissingData()
    {
        // Test trường hợp POST thiếu dữ liệu 'objective'
        $response = $this->http->request('POST', '', [
            'json' => ['courseID' => 'course123']
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu courseID hoặc objective', $body['message']);
    }

    public function testPutWithMissingData()
    {
        // Test trường hợp PUT thiếu dữ liệu
        $response = $this->http->request('PUT', '', [
            'json' => [
                'objectiveID' => 'obj456',
                'courseID' => 'course123'
                // Thiếu 'objective'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu objectiveID, courseID hoặc objective', $body['message']);
    }

    public function testDeleteWithMissingId()
    {
        // Test trường hợp DELETE thiếu objectiveID
        $response = $this->http->request('DELETE', '', [
            'json' => [] // Body rỗng
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu objectiveID', $body['message']);
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
