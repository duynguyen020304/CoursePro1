<?php

// tests/CourseRequirementApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

if (!class_exists('CourseRequirementService')) {
    class CourseRequirementService
    {
        public function get_requirement_by_requirement_id($requirementID) {}
        public function get_requirements_by_course_id($courseID) {}
        public function create($courseID, $requirement) {}
        public function update($requirementID, $courseID, $requirement) {}
        public function delete($requirementID) {}
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


class CourseRequirementApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/path/to/your/api/course_requirement_api.php';

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
        $this->assertEquals('Thiếu requirementID hoặc courseID', $body['message']);
    }

    public function testGetByCourseId()
    {
        $response = $this->http->request('GET', '', ['query' => ['courseID' => 'course123']]);
        
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
        $this->assertEquals('Thiếu courseID hoặc requirement', $body['message']);
    }

    public function testPutWithMissingData()
    {
        $response = $this->http->request('PUT', '', [
            'json' => [
                'requirementID' => 'req456',
                'courseID' => 'course123'
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu objectiveID, courseID hoặc objective', $body['message']);
    }

    public function testDeleteWithMissingId()
    {
        $response = $this->http->request('DELETE', '', [
            'json' => []
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Thiếu requirementID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $response = $this->http->request('PATCH');

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Phương thức không hỗ trợ', $body['message']);
    }
}