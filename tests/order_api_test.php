<?php

// Note: The following is the cleaned version without comments.

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

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
    private $baseUrl = 'http://localhost/path/to/your/api/order_api.php';

    protected function setUp(): void
    {
        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false,
        ]);
    }

    protected function tearDown(): void
    {
        $this->http = null;
    }

    public function testGetWithMissingIds()
    {
        $response = $this->http->request('GET');

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID hoặc userID', $body['message']);
    }

    public function testPostWithMissingData()
    {
        $response = $this->http->request('POST', '', [
            'json' => ['userID' => 'user123']
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu: userID, orderDate, totalAmount', $body['message']);
    }
    
    public function testPutWithMissingData()
    {
        $response = $this->http->request('PUT', '', [
            'json' => [
                'orderID' => 'order456',
                'userID' => 'user123'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu cần cập nhật', $body['message']);
    }

    public function testPutWithInvalidDateFormat()
    {
        $response = $this->http->request('PUT', '', [
            'json' => [
                'orderID' => 'order456',
                'userID' => 'user123',
                'orderDate' => 'invalid-date',
                'totalAmount' => 100.0
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Định dạng orderDate không hợp lệ', $body['message']);
    }

    public function testDeleteWithMissingId()
    {
        $response = $this->http->request('DELETE', '', [
            'json' => []
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $response = $this->http->request('PATCH');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Phương thức không hỗ trợ', $body['message']);
    }
}