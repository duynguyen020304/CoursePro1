<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class CartApiTest extends TestCase
{
    private $http;
    private $secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
    private $baseUrl = 'http://localhost/api/cart_api.php';

    protected function setUp(): void
    {
        $this->http = new Client(['base_uri' => $this->baseUrl]);
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
            'data' => [
                'userID' => $userID
            ]
        ];
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function testShouldReturn401WhenNoTokenIsProvided()
    {
        try {
            $this->http->request('GET');
        } catch (RequestException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Không tìm thấy token xác thực.', $responseBody['message']);
        }
    }

    public function testPostCartMissingUserID()
    {
        $token = $this->generateToken(1, time() + 3600);
        try {
            $this->http->request('POST', '', [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'json' => ['some_other_field' => 'value']
            ]);
        } catch (RequestException $e) {
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Missing userID', $responseBody['error']);
        }
    }

    public function testPutCartMissingData()
    {
        $token = $this->generateToken(1, time() + 3600);
        try {
            $this->http->request('PUT', '', [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'json' => ['cartID' => 123]
            ]);
        } catch (RequestException $e) {
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Missing cartID or userID', $responseBody['error']);
        }
    }

    public function testDeleteCartMissingCartID()
    {
        $token = $this->generateToken(1, time() + 3600);
        try {
            $this->http->request('DELETE', '', [
                'headers' => ['Authorization' => 'Bearer ' . $token]
            ]);
        } catch (RequestException $e) {
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            $this->assertEquals('Missing cartID', $responseBody['error']);
        }
    }
    
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