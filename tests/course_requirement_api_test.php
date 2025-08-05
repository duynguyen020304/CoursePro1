<?php

// tests/CourseRequirementApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('CourseRequirementService')) {
    class CourseRequirementService
    {
        public function get_requirement_by_requirement_id($requirementID) {}
        public function get_requirements_by_course_id($courseID) {}
        public function create($courseID, $requirement) {}
        public function update($requirementID, $courseID, $requirement) {}
        public function delete($requirementID) {}
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


class CourseRequirementApiTest extends TestCase
{
    private $http;
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/course_requirement_api.php';

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
        // Test trường hợp GET mà không cung cấp requirementID hay courseID
        $response = $this->http->request('GET');

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu requirementID hoặc courseID', $body['message']);
    }

    public function testGetByCourseId()
    {
        // Test trường hợp GET thành công với courseID.
        $response = $this->http->request('GET', '', ['query' => ['courseID' => 'course123']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
    }

    public function testPostWithMissingData()
    {
        // Test trường hợp POST thiếu dữ liệu 'requirement'
        $response = $this->http->request('POST', '', [
            'json' => ['courseID' => 'course123']
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu courseID hoặc requirement', $body['message']);
    }

    public function testPutWithMissingData()
    {
        // Test trường hợp PUT thiếu dữ liệu
        $response = $this->http->request('PUT', '', [
            'json' => [
                'requirementID' => 'req456',
                'courseID' => 'course123'
                // Thiếu 'requirement'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        // Lưu ý: API gốc có lỗi copy-paste ở đây, thông báo lỗi nhắc đến 'objective' thay vì 'requirement'
        $this->assertEquals('Thiếu objectiveID, courseID hoặc objective', $body['message']);
    }

    public function testDeleteWithMissingId()
    {
        // Test trường hợp DELETE thiếu requirementID
        $response = $this->http->request('DELETE', '', [
            'json' => [] // Body rỗng
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu requirementID', $body['message']);
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
