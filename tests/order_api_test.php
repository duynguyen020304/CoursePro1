<?php

// tests/OrderApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('OrderService')) {
    class OrderService
    {
        public function get_order_by_order_id($orderID) {}
        public function get_orders_by_user_id($userID) {}
        public function create_order($orderID, $userID, $orderDate, $totalAmount) {}
        public function update_order($orderID, $userID, $orderDate, $totalAmount) {}
        public function delete_order($orderID) {}
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


class OrderApiTest extends TestCase
{
    private $http;
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/order_api.php';

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
        // Test trường hợp GET mà không cung cấp orderID hay userID
        $response = $this->http->request('GET');

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID hoặc userID', $body['message']);
    }

    public function testPostWithMissingData()
    {
        // Test trường hợp POST thiếu dữ liệu 'totalAmount'
        $response = $this->http->request('POST', '', [
            'json' => ['userID' => 'user123']
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu: userID, orderDate, totalAmount', $body['message']);
    }
    
    public function testPutWithMissingData()
    {
        // Test trường hợp PUT thiếu dữ liệu
        $response = $this->http->request('PUT', '', [
            'json' => [
                'orderID' => 'order456',
                'userID' => 'user123'
                // Thiếu orderDate và totalAmount
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu cần cập nhật', $body['message']);
    }

    public function testPutWithInvalidDateFormat()
    {
        // Test trường hợp PUT với định dạng ngày không hợp lệ
        $response = $this->http->request('PUT', '', [
            'json' => [
                'orderID' => 'order456',
                'userID' => 'user123',
                'orderDate' => 'invalid-date', // Định dạng sai
                'totalAmount' => 100.0
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Định dạng orderDate không hợp lệ', $body['message']);
    }

    public function testDeleteWithMissingId()
    {
        // Test trường hợp DELETE thiếu orderID
        $response = $this->http->request('DELETE', '', [
            'json' => [] // Body rỗng
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID', $body['message']);
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
