<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;


class OrderDetailApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/path/to/your/api/order_detail_api.php';

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

    public function testGetWithMissingOrderId()
    {
        $response = $this->http->request('GET');

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID', $body['message']);
    }

    public function testPostWithMissingData()
    {
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
        $response = $this->http->request('PUT', '', [
            'json' => [
                'orderID' => 'order123',
                'price' => 99.99
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu orderID, courseID hoặc price', $body['message']);
    }

    public function testDeleteWithMissingData()
    {
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
        $response = $this->http->request('PATCH');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Phương thức không hỗ trợ', $body['message']);
    }
}