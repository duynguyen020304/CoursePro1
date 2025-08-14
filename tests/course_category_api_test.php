<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

if (!class_exists('CourseCategoryService')) {
    class CourseCategoryService
    {
        public function get_courses_by_category_id($categoryID) {}
        public function get_categories_by_course_id($courseID) {}
        public function add_category_to_course($courseID, $categoryID) {}
        public function unlink_course_category($courseID, $categoryID) {}
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


class CourseCategoryApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/path/to/your/api/course_category_api.php';

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