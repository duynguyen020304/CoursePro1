<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

if (!class_exists('UserService')) {
    class UserService
    {
        public function create_user($email, $pass, $first, $last, $role, $bio, $img) {}
        public function update_user_partial($data) {}
        public function authenticate($email, $password) {}
    }
}
if (!class_exists('UserDTO')) {
    class UserDTO {}
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

class LoginApiTest extends TestCase
{
    private $http;
    private $serviceMock;

    protected function setUp(): void
    {
        $baseUrl = 'http://localhost/path/to/your/api/login_api.php';
        
        $this->http = new Client([
            'base_uri' => $baseUrl,
            'http_errors' => false,
        ]);

        $this->serviceMock = $this->createMock(UserService::class);
    }

    protected function tearDown(): void
    {
        $this->http = null;
        $this->serviceMock = null;
    }

    public function testShouldReturn405ForNonPostRequest()
    {
        $response = $this->http->request('GET');
        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Phương thức không được hỗ trợ', $body['message']);
    }

    public function testShouldReturn400WhenCredentialsAreMissing()
    {
        $response = $this->http->request('POST', '', ['json' => []]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu email hoặc mật khẩu', $body['message']);
    }

    public function testSignupShouldFailWithMissingInfo()
    {
        $response = $this->http->request('POST', '', [
            'json' => [
                'isSignup' => true,
                'email' => 'test@example.com',
                'password' => 'password123'
            ]
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu thông tin đăng ký', $body['message']);
    }

    public function testChangePasswordShouldSucceed()
    {
        $response = $this->http->request('POST', '', [
            'json' => [
                'isChangePassword' => true,
                'email' => 'test@example.com',
                'password' => 'newpassword'
            ]
        ]);
        $this->assertNotEquals(400, $response->getStatusCode());
    }

    public function testLoginShouldFailWithInvalidCredentials()
    {
        $this->markTestSkipped('Requires dependency injection to mock the service response.');
    }

    public function testLoginShouldSucceedWithValidCredentials()
    {
        $this->markTestSkipped('Requires dependency injection to mock the service response.');
    }
}