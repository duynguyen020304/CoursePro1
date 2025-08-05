<?php

// tests/OrderDetailApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('OrderDetailService')) {
    class OrderDetailService
    {
        public function get_details_by_order($orderID) {}
        public function add_detail($orderID, $courseID, $price) {}
        public function update_detail($orderID, $courseID, $price) {}
        public function delete_detail($orderID, $courseID) {}
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


class OrderDetailApiTest extends TestCase
{
    private $http;
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/order_detail_api.php';

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

    public function testGetWithMissingOrderId()
    {
        // Test trường hợp GET mà không cung cấp orderID
        $response = $this->http->request('GET');

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID', $body['message']);
    }

    public function testPostWithMissingData()
    {
        // Test trường hợp POST thiếu dữ liệu 'price'
        $response = $this->http->request('POST', '', [
            'json' => [
                'orderID' => 'order123',
                'courseID' => 'course456'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID, courseID hoặc price', $body['message']);
    }
    
    public function testPutWithMissingData()
    {
        // Test trường hợp PUT thiếu dữ liệu
        $response = $this->http->request('PUT', '', [
            'json' => [
                'orderID' => 'order123',
                'price' => 99.99
                // Thiếu courseID
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID, courseID hoặc price', $body['message']);
    }

    public function testDeleteWithMissingData()
    {
        // Test trường hợp DELETE thiếu courseID
        $response = $this->http->request('DELETE', '', [
            'json' => [
                'orderID' => 'order123'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID hoặc courseID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        // Sử dụng phương thức PATCH không được hỗ trợ
        $response = $this->http->request('PATCH');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Phương thức không hỗ trợ', $body['message']);
    }
}
