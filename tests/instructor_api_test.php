<?php

// tests/InstructorApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('InstructorService')) {
    class InstructorService
    {
        public function get_instructor($instructorID) {}
        public function get_all_instructors() {}
        public function create_instructor($instructorID, $userID, $biography) {}
        public function update_instructor($instructorID, $userID, $biography, $profileImage) {}
        public function delete_instructor($instructorID) {}
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


class InstructorApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/instructor_api.php';

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
    public function testPostShouldFailWithoutToken()
    {
        // Các phương thức POST, PUT, DELETE đều yêu cầu token
        $response = $this->http->request('POST', '', ['json' => []]);
        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Không tìm thấy token xác thực.', $body['message']);
    }

    public function testGetShouldFailWithoutTokenAndFlag()
    {
        // GET thông thường (không có flag) vẫn cần token
        $response = $this->http->request('GET');
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testGetShouldSucceedWithoutTokenWhenHomePageFlagIsSet()
    {
        // Đây là trường hợp đặc biệt, GET được phép truy cập không cần token
        // nếu có cờ isGetInstructorHomePage=true
        $response = $this->http->request('GET', '', ['query' => ['isGetInstructorHomePage' => 'true']]);
        
        // Mong đợi response thành công (200), không phải 401
        $this->assertEquals(200, $response->getStatusCode());
    }

    // --- Test Logic các phương thức ---
    public function testGetByInstructorIdRequiresToken()
    {
        $token = $this->generateToken();
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'query' => ['instructorID' => 'instr123']
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
    }

    public function testPostWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => ['instructorID' => 'instr123'] // Thiếu userID
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu instructorID hoặc userID', $body['message']);
    }

    public function testPutWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PUT', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => ['userID' => 'user456'] // Thiếu instructorID
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu instructorID hoặc userID', $body['message']);
    }

    public function testDeleteWithMissingId()
    {
        $token = $this->generateToken();
        $response = $this->http->request('DELETE', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [] // Body rỗng
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu instructorID để xóa', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PATCH', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Phương thức không được hỗ trợ', $body['message']);
    }
}
