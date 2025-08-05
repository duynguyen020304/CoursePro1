<?php

// tests/ReviewApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('ReviewService')) {
    class ReviewService
    {
        public function get_reviews_by_course($courseID) {}
        public function create_review($reviewID, $userID, $courseID, $rating, $comment) {}
        public function update_review($reviewID, $userID, $courseID, $rating, $comment) {}
        public function delete_review($reviewID) {}
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


class ReviewApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/review_api.php';

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

    public function testShouldReturn401ForExpiredToken()
    {
        $payload = [
            'iat' => time() - 3601,
            'exp' => time() - 3600,
            'data' => ['userID' => 1]
        ];
        $expiredToken = JWT::encode($payload, $this->secretKey, 'HS256');

        $response = $this->http->request('POST', [
            'headers' => ['Authorization' => 'Bearer ' . $expiredToken]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Token đã hết hạn.', $body['message']);
    }

    // --- Test Logic các phương thức ---
    public function testGetWithMissingCourseId()
    {
        $token = $this->generateToken();
        $response = $this->http->request('GET', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
            // Không có query param
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu courseID', $body['message']);
    }

    public function testPostWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'reviewID' => 'rev123',
                'userID' => 'user456',
                'courseID' => 'course789'
                // Thiếu rating
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu', $body['message']);
    }

    public function testPutWithMissingData()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PUT', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'reviewID' => 'rev123',
                'userID' => 'user456'
                // Thiếu courseID và rating
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu', $body['message']);
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
        $this->assertEquals('Thiếu reviewID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PATCH', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Phương thức không hỗ trợ', $body['message']);
    }
}
