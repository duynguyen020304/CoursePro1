<?php

// tests/CartApiTest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

// Mock the service class so we can control its behavior during tests
// This avoids hitting the actual database
if (!class_exists('CartService')) {
    class CartService
    {
        public function get_cart_by_user($userID) {}
        public function create_cart($userID) {}
        public function update_cart($cartID, $userID) {}
        public function delete_cart($cartID) {}
    }
}


class CartApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    private $baseUrl = 'http://localhost/path/to/your/api/cart_api.php'; // <-- IMPORTANT: Change this to your actual local URL

    protected function setUp(): void
    {
        // Setup Guzzle client to make HTTP requests to the API
        $this->http = new Client(['base_uri' => $this->baseUrl]);
    }

    protected function tearDown(): void
    {
        // Clean up resources
        $this->http = null;
    }

    /**
     * Generates a JWT token for testing purposes.
     *
     * @param int $userID The user ID to include in the token payload.
     * @param int $expirationTime The token's expiration time.
     * @return string The generated JWT.
     */
    private function generateToken(int $userID, int $expirationTime): string
    {
        $payload = [
            'iss' => 'your_issuer', // Issuer
            'aud' => 'your_audience', // Audience
            'iat' => time(), // Issued at
            'exp' => $expirationTime, // Expiration time
            'data' => [
                'userID' => $userID
            ]
        ];
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * Test case for when no authentication token is provided.
     * Expects a 401 Unauthorized response.
     */
    public function testShouldReturn401WhenNoTokenIsProvided()
    {
        try {
            $this->http->request('GET');
        } catch (RequestException $e) {
            // Assert that the response status code is 401
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            // Assert the error message
            $this->assertEquals('Không tìm thấy token xác thực.', $responseBody['message']);
        }
    }

    /**
     * Test case for when an expired token is provided.
     * Expects a 401 Unauthorized response.
     */
    public function testShouldReturn401ForExpiredToken()
    {
        // Generate a token that expired in the past
        $expiredToken = $this->generateToken(1, time() - 3600);

        try {
            $this->http->request('GET', '', [
                'headers' => ['Authorization' => 'Bearer ' . $expiredToken]
            ]);
        } catch (RequestException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Token đã hết hạn.', $responseBody['message']);
        }
    }
    
    /**
     * Test case for an invalid signature.
     * Expects a 401 Unauthorized response.
     */
    public function testShouldReturn401ForInvalidSignature()
    {
        $payload = [
            'iss' => 'your_issuer',
            'aud' => 'your_audience',
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => ['userID' => 1]
        ];
        // Encode with a wrong key
        $invalidToken = JWT::encode($payload, 'wrong-secret-key', 'HS256');

        try {
            $this->http->request('GET', '', [
                'headers' => ['Authorization' => 'Bearer ' . $invalidToken]
            ]);
        } catch (RequestException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Chữ ký token không hợp lệ.', $responseBody['message']);
        }
    }


    /**
     * Test GET /cart_api.php
     * This test simulates the service layer and checks if the API returns the correct cart ID.
     */
    public function testGetCartSuccess()
    {
        // We need to mock the CartService to control its return value
        $cartServiceMock = $this->createMock(CartService::class);
        
        // We expect the 'get_cart_by_user' method to be called once with userID 1
        // and we want it to return a mock cart object.
        $mockCart = new stdClass();
        $mockCart->cartID = 123;
        $cartServiceMock->expects($this->once())
                        ->method('get_cart_by_user')
                        ->with($this->equalTo(1))
                        ->willReturn($mockCart);

        // Here we would need a way to inject this mock into the API script.
        // Since we are doing black-box testing with Guzzle, this is tricky.
        // A better approach would be to refactor the API to allow dependency injection.
        // For now, we'll assume the service works and test the API's response structure.
        
        // Note: The following part of the test will likely fail unless you run this against
        // a live server where you can guarantee the state of the database or mock the service.
        // For a true unit test, you would not use a real HTTP client but would instead
        // include the PHP file and mock global variables like $_SERVER.
        
        $this->markTestSkipped(
            'This test requires a live server and a way to mock the service layer, which is complex in this setup.'
        );

        // Example of how the test would look if we could proceed:
        // $token = $this->generateToken(1, time() + 3600);
        // $response = $this->http->request('GET', '', [
        //     'headers' => ['Authorization' => 'Bearer ' . $token]
        // ]);
        // $this->assertEquals(200, $response->getStatusCode());
        // $body = json_decode($response->getBody(), true);
        // $this->assertTrue($body['sucesss']);
        // $this->assertEquals(123, $body['cartID']);
    }
    
    /**
     * Test POST /cart_api.php with missing data.
     */
    public function testPostCartMissingUserID()
    {
        $token = $this->generateToken(1, time() + 3600);
        try {
            $this->http->request('POST', '', [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'json' => ['some_other_field' => 'value'] // No userID
            ]);
        } catch (RequestException $e) {
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Missing userID', $responseBody['error']);
        }
    }

    /**
     * Test PUT /cart_api.php with missing data.
     */
    public function testPutCartMissingData()
    {
        $token = $this->generateToken(1, time() + 3600);
        try {
            $this->http->request('PUT', '', [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'json' => ['cartID' => 123] // Missing userID
            ]);
        } catch (RequestException $e) {
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Missing cartID or userID', $responseBody['error']);
        }
    }

    /**
     * Test DELETE /cart_api.php with missing cartID.
     */
    public function testDeleteCartMissingCartID()
    {
        $token = $this->generateToken(1, time() + 3600);
        try {
            // The cartID is expected as a query parameter
            $this->http->request('DELETE', '', [
                'headers' => ['Authorization' => 'Bearer ' . $token]
            ]);
        } catch (RequestException $e) {
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Missing cartID', $responseBody['error']);
        }
    }
    
    /**
     * Test using a method that is not allowed (e.g., PATCH).
     */
    public function testMethodNotAllowed()
    {
        $token = $this->generateToken(1, time() + 3600);
        try {
            $this->http->request('PATCH', '', [
                'headers' => ['Authorization' => 'Bearer ' . $token]
            ]);
        } catch (RequestException $e) {
            $this->assertEquals(405, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Method not allowed', $responseBody['error']);
        }
    }
}
