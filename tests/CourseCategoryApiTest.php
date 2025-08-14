<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class CourseCategoryApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/api/course_category_api.php';

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
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu courseID', $body['message']);
    }

    public function testGetByCourseId()
    {
        $response = $this->http->request('GET', '', ['query' => ['courseID' => 'course123']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
    }

    public function testGetByCategoryId()
    {
        $response = $this->http->request('GET', '', ['query' => ['categoryID' => 'cat456']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
    }

    public function testPostWithMissingData()
    {
        $response = $this->http->request('POST', '', [
            'json' => ['courseID' => 'course123']
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu courseID hoặc categoryID', $body['message']);
    }

    public function testDeleteWithMissingData()
    {
        $response = $this->http->request('DELETE', '', [
            'json' => ['categoryID' => 'cat456']
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu courseID hoặc categoryID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $response = $this->http->request('PUT');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Phương thức không được hỗ trợ', $body['message']);
    }
}