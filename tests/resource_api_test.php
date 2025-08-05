<?php

// tests/ResourceApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('ResourceService')) {
    class ResourceService
    {
        public function get_resource_by_resource_id($resourceID) {}
        public function get_resources_by_lesson_id($lessonID) {}
        public function get_all_resources() {}
        public function create_resource($lessonID, $resourcePath, $title, $sortOrder) {}
        public function update_resource($resourceID, $lessonID, $resourcePath, $title, $sortOrder) {}
        public function delete_resource($resourceID) {}
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


class ResourceApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/resource_api.php';

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
    public function testGetWithoutIdShouldGetAllResources()
    {
        $token = $this->generateToken();
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
            // Không có query param, API sẽ gọi get_all_resources
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'lessonID' => 'lesson123'
                // Thiếu resourcePath
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu lessonID hoặc resourcePath', $body['message']);
    }

    public function testPutWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PUT', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'resourceID' => 'res456',
                'lessonID' => 'lesson123'
                // Thiếu resourcePath
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu resourceID, lessonID hoặc resourcePath', $body['message']);
    }
    
    public function testDeleteWithMissingId()
    {
        $token = $this->generateToken();
        // Gọi DELETE không có ID trong cả query và body
        $response = $this->http->request('DELETE', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu resourceID để xóa', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PATCH', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals("Phương thức PATCH không được hỗ trợ", $body['message']);
    }
}
