<?php

// tests/LoginApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

// Mock các class phụ thuộc để môi trường test không bị lỗi
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
        // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
        $baseUrl = 'http://localhost/path/to/your/api/login_api.php';
        
        $this->http = new Client([
            'base_uri' => $baseUrl,
            'http_errors' => false,
        ]);

        // Tạo mock cho UserService để kiểm soát kết quả trả về của nó
        $this->serviceMock = $this->createMock(UserService::class);
    }

    protected function tearDown(): void
    {
        $this->http = null;
        $this->serviceMock = null;
    }

    // --- Bắt đầu các Test Case ---

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

    // --- Test Đăng ký (Signup) ---
    public function testSignupShouldFailWithMissingInfo()
    {
        $response = $this->http->request('POST', '', [
            'json' => [
                'isSignup' => true,
                'email' => 'test@example.com',
                'password' => 'password123'
                // Thiếu firstname, lastname, role
            ]
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu thông tin đăng ký', $body['message']);
    }

    // --- Test Đổi mật khẩu ---
    public function testChangePasswordShouldSucceed()
    {
        // Giả lập service trả về thành công
        // Lưu ý: Để test này chạy đúng, bạn cần cơ chế Dependency Injection
        // Ở đây, chúng ta chỉ kiểm tra logic validation của API
        $response = $this->http->request('POST', '', [
            'json' => [
                'isChangePassword' => true,
                'email' => 'test@example.com',
                'password' => 'newpassword'
            ]
        ]);
        // API sẽ cố gắng gọi service, nhưng vì không mock được, nó có thể trả về 500
        // hoặc 200 tùy vào service của bạn. Test này chủ yếu để đảm bảo nó không trả về 400.
        $this->assertNotEquals(400, $response->getStatusCode());
    }

    // --- Test Đăng nhập ---
    public function testLoginShouldFailWithInvalidCredentials()
    {
        // Test này sẽ thất bại nếu không có Dependency Injection
        // vì chúng ta không thể điều khiển kết quả của $service->authenticate
        $this->markTestSkipped('Requires dependency injection to mock the service response.');
        
        // Ví dụ nếu có DI:
        // $this->serviceMock->method('authenticate')->willReturn(new ServiceResponse(false, 'Sai mật khẩu'));
        // ... thực hiện request ...
        // $this->assertEquals(401, $response->getStatusCode());
    }

    public function testLoginShouldSucceedWithValidCredentials()
    {
        // Test này cũng sẽ thất bại nếu không có Dependency Injection
        $this->markTestSkipped('Requires dependency injection to mock the service response.');

        // Ví dụ nếu có DI:
        // $userData = new stdClass();
        // $userData->userID = 'user1'; ...
        // $this->serviceMock->method('authenticate')->willReturn(new ServiceResponse(true, 'Success', $userData));
        // ... thực hiện request ...
        // $this->assertEquals(200, $response->getStatusCode());
        // $body = json_decode($response->getBody(), true);
        // $this->assertArrayHasKey('token', $body);
    }
}
