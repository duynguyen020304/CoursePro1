<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

if (!class_exists('CourseService')) {
    class CourseService
    {
        public function search_courses_by_title_for_course_management($title) {}
        public function search_courses_by_title($title) {}
        public function get_all_courses() {}
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


class SearchCourseApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/path/to/your/api/search_course_api.php';

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

    public function testSearchForCourseManagement()
    {
        $response = $this->http->request('GET', '', [
            'query' => [
                'isGetForCourseManagement' => 'true',
                'title' => 'PHP'
            ]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('data', $body);
    }

    public function testSearchByTitleOnly()
    {
        $response = $this->http->request('GET', '', [
            'query' => ['title' => 'Java']
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertIsArray($body);
    }

    public function testSearchWithoutTitle()
    {
        $response = $this->http->request('GET');
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertIsArray($body);
    }

    public function testInvalidRequestMethod()
    {
        $response = $this->http->request('POST');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Phương thức không được hỗ trợ', $body['message']);
    }
}