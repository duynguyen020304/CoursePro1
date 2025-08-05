<?php

// tests/CourseInstructorApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('CourseInstructorService')) {
    class CourseInstructorService
    {
        public function get_instructors_by_course_id($courseID) {}
        public function add($courseID, $instructorID) {}
        public function update($oldCourseID, $oldInstructorID, $newCourseID, $newInstructorID) {}
        public function unlink_course_instructor($courseID, $instructorID) {}
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


class CourseInstructorApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/course_instructor_api.php';

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

    private function generateToken(int $userID = 1): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => ['userID' => $userID]
        ];
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    // --- Bắt đầu các Test Case ---

    // --- Test Xác thực ---
    public function testShouldReturn401WhenNoTokenIsProvided()
    {
        $response = $this->http->request('GET');
        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Không tìm thấy token xác thực.', $body['message']);
    }

    public function testShouldReturn401ForInvalidSignature()
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => ['userID' => 1]
        ];
        $invalidToken = JWT::encode($payload, 'wrong-secret-key', 'HS256');
        $response = $this->http->request('POST', [
            'headers' => ['Authorization' => 'Bearer ' . $invalidToken]
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Chữ ký token không hợp lệ.', $body['message']);
    }

    // --- Test Logic các phương thức ---
    public function testGetWithMissingCourseId()
    {
        $token = $this->generateToken();
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
            // Không có query param 'courseID'
        ]);

        $this->assertEquals(200, $response->getStatusCode()); // API trả về 200 nhưng success=false
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu parameter: courseID', $body['message']);
    }

    public function testPostWithoutBody()
    {
        // API này không có validation ở tầng controller, nó sẽ truyền chuỗi rỗng
        // vào service. Vì vậy, chúng ta chỉ kiểm tra xem nó có trả về 200 OK không.
        $token = $this->generateToken();
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => []
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testPutWithoutBody()
    {
        // Tương tự POST, chỉ kiểm tra response code
        $token = $this->generateToken();
        $response = $this->http->request('PUT', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => []
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteWithoutBody()
    {
        // Tương tự POST, chỉ kiểm tra response code
        $token = $this->generateToken();
        $response = $this->http->request('DELETE', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => []
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken();
        // Sử dụng phương thức PATCH không được hỗ trợ
        $response = $this->http->request('PATCH', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Method Not Allowed', $body['message']);
    }
}
