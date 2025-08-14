<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class SearchCourseApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/api/search_course_api.php';

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