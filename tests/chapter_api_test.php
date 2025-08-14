<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

if (!class_exists('ChapterService')) {
    class ChapterService
    {
        public function get_chapters_by_course_id($courseID) {}
        public function get_all_chapters() {}
        public function create_chapter($courseID, $title, $description, $sortOrder) {}
        public function update_chapter($chapterID, $courseID, $title, $description, $sortOrder) {}
        public function delete_chapter($id) {}
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


class ChapterApiTest extends TestCase
{
    private $http;
    private $baseUrl = 'http://localhost/path/to/your/api/chapter_api.php';

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

    private function generateToken(int $userID, int $expirationTime): string
    {
        $payload = [
            'iss' => 'your_issuer',
            'aud' => 'your_audience',
            'iat' => time(),
            'exp' => $expirationTime,
            'data' => ['userID' => $userID]
        ];
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function testGetChaptersWithoutCourseId()
    {
        $response = $this->http->request('GET');
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('data', $body);
    }

    public function testGetChaptersWithCourseId()
    {
        $response = $this->http->request('GET', '', ['query' => ['courseID' => 'course123']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('data', $body);
    }

    public function testPostChapter()
    {
        $response = $this->http->request('POST', '', [
            'json' => [
                'courseID' => 'course123',
                'title' => 'New Chapter'
            ]
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
    }

    public function testPutChapter()
    {
        $response = $this->http->request('PUT', '', [
            'json' => [
                'chapterID' => 'chap456',
                'courseID' => 'course123',
                'title' => 'Updated Chapter'
            ]
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $body);
    }

    public function testDeleteChapterWithMissingId()
    {
        $response = $this->http->request('DELETE');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }

    public function testDeleteChapterWithId()
    {
        $response = $this->http->request('DELETE', '', ['query' => ['id' => 'chap456']]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }

    public function testInvalidRequestMethod()
    {
        $response = $this->http->request('PATCH');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }
}