<?php

use GrinchenkoUniversity\Diia\Client\AuthClient;
use GrinchenkoUniversity\Diia\Dto\Credentials;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AuthClientTest extends TestCase
{
    private Client $httpClient;
    private AuthClient $authClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->authClient = new AuthClient($this->httpClient);
    }

    /**
     * @dataProvider authDataProvider
     *
     * @param Credentials $credentials
     * @param array $headers
     *
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAuthenticated(Credentials $credentials, array $headers): void
    {
        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            '{"token": "eyJ...ePg"}'
        );

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api/v1/auth/acquirer/acquirerToken',
                [
                    'headers' => $headers,
                ]
            )
            ->willReturn($response)
        ;

        $token = $this->authClient
            ->acquireToken($credentials)
        ;

        $this->assertEquals('eyJ...ePg', $token);
    }

    public function authDataProvider(): Generator
    {
        yield 'with auth_acquirer_token' => [
            new Credentials('acquirerToken', 'authAcquirerToken'),
            [
                'accept' => 'application/json',
                'Authorization' => 'Basic authAcquirerToken'
            ]
        ];

        yield 'without auth_acquirer_token' => [
            new Credentials('acquirerToken', null),
            [
                'accept' => 'application/json',
            ]
        ];
    }
}
