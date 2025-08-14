<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

if (!class_exists('CourseService')) {
    class CourseService
    {
        public function get_courses_paginated_service($page, $size, $diff, $lang) {}
        public function get_courses_by_difficulty_lang_service($diff, $lang) {}
        public function get_all_courses() {}
        public function get_k_courses_for_home_page($k) {}
        public function get_all_courses_for_course_management() {}
        public function get_all_courses_for_upload_video() {}
        public function get_course_by_id_for_category_filter($id) {}
        public function get_course_for_recommend($id) {}
        public function get_course_by_id($id) {}
        public function create_course($title, $desc, $price, $instructors, $cats, $diff, $lang, $creator) {}
        public function update_course($id, $title, $desc, $price, $instructors, $cats, $diff, $lang) {}
        public function delete_course($id) {}
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


class CourseApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    private $baseUrl = 'http://localhost/path/to/your/api/course_api.php';

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

    private function generateToken(int $userID = 1): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => ['userID' => $userID]
        ];
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function testPostShouldFailWithoutToken()
    {
        $response = $this->http->request('POST', '', ['json' => []]);
        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Không tìm thấy token xác thực.', $body['message']);
    }

    public function testGetIsPubliclyAccessible()
    {
        $response = $this->http->request('GET');
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    public function testGetWithInvalidPageParameter()
    {
        $response = $this->http->request('GET', '', ['query' => ['page' => 'invalid']]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('page không hợp lệ. Phải là một số nguyên dương.', $body['message']);
    }

    public function testGetWithInvalidPageSizeParameter()
    {
        $response = $this->http->request('GET', '', ['query' => ['page' => 1, 'pageSize' => 0]]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('pageSize không hợp lệ. Phải là một số nguyên dương.', $body['message']);
    }

    public function testGetWithUnmatchedParameters()
    {
        $response = $this->http->request('GET', '', ['query' => ['foo' => 'bar']]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Invalid or missing GET parameters, and no specific route matched.', $body['message']);
    }

    public function testPostWithMissingRequiredFields()
    {
        $token = $this->generateToken();
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'title' => 'Test Course',
            ]
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertStringContainsString('Thiếu các dữ liệu đầu vào bắt buộc', $body['message']);
    }

    public function testPostWithInvalidArrayFields()
    {
        $token = $this->generateToken();
        $response = $this->http->request('POST', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'title' => 'Test Course',
                'price' => 100,
                'difficulty' => 'easy',
                'language' => 'English',
                'instructorsID' => [],
                'categoriesID' => [1, 2]
            ]
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Các trường sau phải là mảng không rỗng: instructorsID', $body['message']);
    }

    public function testPutWithMissingCourseId()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PUT', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [
                'title' => 'Updated Title',
                'price' => 100,
                'difficulty' => 'easy',
                'language' => 'English',
                'instructorsID' => [1],
                'categoriesID' => [1]
            ]
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertStringContainsString('Thiếu các dữ liệu cần cập nhật bắt buộc: courseID', $body['message']);
    }

    public function testDeleteWithMissingCourseId()
    {
        $token = $this->generateToken();
        $response = $this->http->request('DELETE', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => []
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu dữ liệu cần thiết để xóa: courseID', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PATCH', '', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);
        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Phương thức không được hỗ trợ', $body['message']);
    }
}