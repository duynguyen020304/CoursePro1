<?php

// tests/UserApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

// Mock các class phụ thuộc để môi trường test không bị lỗi
if (!class_exists('UserService')) {
    class UserService
    {
        public function get_user_by_user_id($userID) {}
        public function get_all_users() {}
        public function create_user($email, $pass, $first, $last, $role, $bio, $img) {}
        public function update_user_partial($data, $requestingUserID) {}
        public function delete_user($userID, $requestingUserID) {}
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


class UserApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    // QUAN TRỌNG: Hãy thay đổi URL này thành URL thực tế của bạn
    private $baseUrl = 'http://localhost/path/to/your/api/user_api.php';

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

    private function generateToken(string $roleID = 'user', int $userID = 1): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => [
                'userID' => $userID,
                'roleID' => $roleID
            ]
        ];
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    // --- Test Xác thực & Phân quyền ---
    public function testGetIsPubliclyAccessible()
    {
        $response = $this->http->request('GET');
        // GET không yêu cầu token, nên phải trả về 200 (hoặc 500 nếu service lỗi), không phải 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    public function testPostFailsWithoutToken()
    {
        $response = $this->http->request('POST', ['json' => []]);
        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Không tìm thấy token xác thực.', $body['message']);
    }
    
    public function testPostFailsWithNonAdminToken()
    {
        $userToken = $this->generateToken('user'); // Token của người dùng thường
        $response = $this->http->request('POST', [
            'headers' => ['Authorization' => 'Bearer ' . $userToken],
            'json' => []
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Không đủ quyền đủ xóa', $body['message']); // API có vẻ dùng sai message
    }

    public function testDeleteFailsWithNonAdminToken()
    {
        $userToken = $this->generateToken('user');
        $response = $this->http->request('DELETE', [
            'headers' => ['Authorization' => 'Bearer ' . $userToken],
            'json' => ['userID' => 'userToDelete']
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Không đủ quyền đủ xóa', $body['message']);
    }

    // --- Test Logic các phương thức ---

    public function testGetShouldRemovePasswordFromResponse()
    {
        // Test này sẽ thất bại nếu không có Dependency Injection để mock service
        $this->markTestSkipped('Requires dependency injection to test password removal.');
        
        // Ví dụ nếu có DI:
        // $mockService->method('get_user_by_user_id')->willReturn(
        //     new ServiceResponse(true, 'OK', (object)['userID' => 1, 'password' => 'hashed_pass'])
        // );
        // $response = $this->http->request('GET', '?id=1');
        // $body = json_decode($response->getBody(), true);
        // $this->assertArrayNotHasKey('password', $body['data']);
    }

    public function testPostWithMissingData()
    {
        $adminToken = $this->generateToken('admin');
        $response = $this->http->request('POST', [
            'headers' => ['Authorization' => 'Bearer ' . $adminToken],
            'json' => [
                'email' => 'test@example.com',
                'password' => 'password123'
                // Thiếu các trường khác
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu thông tin đăng ký', $body['message']);
    }

    public function testPutWithMissingId()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PUT', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'json' => [] // Body rỗng
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu userID', $body['message']);
    }

    public function testDeleteWithMissingId()
    {
        $adminToken = $this->generateToken('admin');
        $response = $this->http->request('DELETE', [
            'headers' => ['Authorization' => 'Bearer ' . $adminToken],
            'json' => [] // Body rỗng
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Thiếu userID để xóa', $body['message']);
    }

    public function testInvalidRequestMethod()
    {
        $token = $this->generateToken();
        $response = $this->http->request('PATCH', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertEquals(405, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Phương thức không được hỗ trợ', $body['message']);
    }
}
