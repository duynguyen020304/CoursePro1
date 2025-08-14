<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

class UserApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
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

    public function testGetIsPubliclyAccessible()
    {
        $response = $this->http->request('GET');
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
        $userToken = $this->generateToken('user');
        $response = $this->http->request('POST', [
            'headers' => ['Authorization' => 'Bearer ' . $userToken],
            'json' => []
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Không đủ quyền đủ xóa', $body['message']);
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

    public function testGetShouldRemovePasswordFromResponse()
    {
        $this->markTestSkipped('Requires dependency injection to test password removal.');
    }

    public function testPostWithMissingData()
    {
        $adminToken = $this->generateToken('admin');
        $response = $this->http->request('POST', [
            'headers' => ['Authorization' => 'Bearer ' . $adminToken],
            'json' => [
                'email' => 'test@example.com',
                'password' => 'password123'
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
            'json' => []
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
            'json' => []
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