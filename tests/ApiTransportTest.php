<?php

use GrinchenkoUniversity\Diia\Client\AuthClient;
use GrinchenkoUniversity\Diia\Dto\Credentials;
use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Http\ApiTransport;
use GrinchenkoUniversity\Diia\Provider\BearerTokenProvider;
use GrinchenkoUniversity\Diia\Tests\Support\ApiFixtures;
use GrinchenkoUniversity\Diia\Tests\Support\InMemoryCache;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ApiTransportTest extends TestCase
{
    use ApiFixtures;
    use InMemoryCache;

    /**
     * @param string|Response ...$responses
     */
    private function transport(array $cached, ...$responses): ApiTransport
    {
        $http = $this->httpClientReplaying(...$responses);
        $tokenProvider = new BearerTokenProvider(
            new AuthClient($http),
            new Credentials('acquirerToken', null),
            $this->inMemoryCache($cached)
        );

        return new ApiTransport($http, $tokenProvider);
    }

    private static function token(string $token): Response
    {
        return new Response(200, [], json_encode(['token' => $token]));
    }

    public function testSendsFixtureRequestAndDecodesResponse(): void
    {
        $fixture = $this->loadFixture('update_branch');
        $transport = $this->transport(['token' => 'eyJ...ePg'], 'update_branch');

        $data = $transport->request('PUT', $fixture['request']['path'], $fixture['request']['body']);

        $this->assertRequestMatchesFixture('update_branch');
        $this->assertSame(['_id' => 'xLm0g93Ghg329NhQj235hAsg32'], $data);
    }

    public function testSendsQueryWithoutBody(): void
    {
        $transport = $this->transport(['token' => 'eyJ...ePg'], 'list_offers');

        $transport->request('GET', '/api/v1/acquirers/branch/branch_id/offers', null, ['skip' => 0, 'limit' => 100]);

        $this->assertRequestMatchesFixture('list_offers');
    }

    public function testEmptyResponseBodyIsEmptyArray(): void
    {
        $transport = $this->transport(['token' => 'eyJ...ePg'], 'delete_branch');

        $this->assertSame([], $transport->request('DELETE', '/api/v2/acquirers/branch/xLm0g93Ghg329NhQj235hAsg32'));
        $this->assertRequestMatchesFixture('delete_branch');
    }

    public function testFetchesTokenOnceAndReusesIt(): void
    {
        $transport = $this->transport([], self::token('token-1'), 'delete_branch', 'delete_branch');

        $transport->request('DELETE', '/api/v2/acquirers/branch/1');
        $transport->request('DELETE', '/api/v2/acquirers/branch/2');

        $this->assertSame(3, $this->recordedRequestCount());
        $this->assertSame('/api/v1/auth/acquirer/acquirerToken', $this->recordedRequest(0)->getUri()->getPath());
        $this->assertSame('Bearer token-1', $this->recordedRequest(2)->getHeaderLine('Authorization'));
    }

    public function testRetriesOnceWithNewTokenAfter401(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $http = $this->httpClientReplaying(
            new Response(401, [], '{"message": "Unauthorized"}'),
            self::token('token-2'),
            'get_branch'
        );
        $transport = new ApiTransport(
            $http,
            new BearerTokenProvider(
                new AuthClient($http),
                new Credentials('acquirerToken', null),
                $this->inMemoryCache(['token' => 'expired'])
            ),
            $logger
        );

        $data = $transport->request('GET', '/api/v2/acquirers/branch/xLm0g93Ghg329NhQj235hAsg32');

        $this->assertSame('xLm0g93Ghg329NhQj235hAsg32', $data['_id']);
        $this->assertSame(3, $this->recordedRequestCount());
        $this->assertSame('Bearer expired', $this->recordedRequest(0)->getHeaderLine('Authorization'));
        $this->assertSame('/api/v1/auth/acquirer/acquirerToken', $this->recordedRequest(1)->getUri()->getPath());
        $this->assertSame('Bearer token-2', $this->recordedRequest(2)->getHeaderLine('Authorization'));
        $this->assertSame('token-2', $this->cachedValue('token'));
    }

    public function testGivesUpAfterSecond401(): void
    {
        $transport = $this->transport(
            ['token' => 'expired'],
            new Response(401, [], '{"message": "Unauthorized"}'),
            self::token('token-2'),
            new Response(401, [], '{"message": "Unauthorized"}'),
            // Would be consumed by a second retry, which must not happen.
            'get_branch'
        );

        try {
            $transport->request('GET', '/api/v2/acquirers/branch/xLm0g93Ghg329NhQj235hAsg32');
            $this->fail('DiiaApiException expected');
        } catch (DiiaApiException $exception) {
            $this->assertSame(401, $exception->getStatusCode());
        }

        $this->assertSame(3, $this->recordedRequestCount());
    }

    public function testMapsErrorBody(): void
    {
        $transport = $this->transport(
            ['token' => 'eyJ...ePg'],
            new Response(422, [], '{"message": "Branch name is required", "code": 1022}')
        );

        try {
            $transport->request('POST', '/api/v2/acquirers/branch', ['name' => '']);
            $this->fail('DiiaApiException expected');
        } catch (DiiaApiException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
            $this->assertSame('1022', $exception->getErrorCode());
            $this->assertSame('{"message": "Branch name is required", "code": 1022}', $exception->getResponseBody());
            $this->assertStringContainsString('POST /api/v2/acquirers/branch', $exception->getMessage());
            $this->assertStringContainsString('HTTP 422', $exception->getMessage());
            $this->assertStringContainsString('Branch name is required', $exception->getMessage());
        }
    }

    public function testMapsNonJsonErrorBody(): void
    {
        $transport = $this->transport(['token' => 'eyJ...ePg'], new Response(502, [], '<html>Bad gateway</html>'));

        try {
            $transport->request('GET', '/api/v2/acquirers/branches');
            $this->fail('DiiaApiException expected');
        } catch (DiiaApiException $exception) {
            $this->assertSame(502, $exception->getStatusCode());
            $this->assertNull($exception->getErrorCode());
            $this->assertSame('<html>Bad gateway</html>', $exception->getResponseBody());
        }
    }

    public function testMapsConnectionFailure(): void
    {
        $previous = new ConnectException(
            'cURL error 7: Connection refused for https://api.diia.test/api/v1/acquirers/document-request/status?barcode=3535267635434',
            new Request('GET', '/api/v1/acquirers/document-request/status'),
            null,
            ['error' => 'Connection refused']
        );
        $transport = $this->transport(['token' => 'eyJ...ePg'], $previous);

        try {
            $transport->request('GET', '/api/v1/acquirers/document-request/status', null, ['barcode' => '3535267635434']);
            $this->fail('DiiaApiException expected');
        } catch (DiiaApiException $exception) {
            $this->assertSame(0, $exception->getStatusCode());
            $this->assertSame($previous, $exception->getPrevious());
            $this->assertSame(
                'Diia API GET /api/v1/acquirers/document-request/status failed: no response (Connection refused)',
                $exception->getMessage()
            );
        }
    }

    public function testMapsFailedTokenRequestWithoutLeakingToken(): void
    {
        $transport = $this->transport([], new Response(403, [], '{"message": "Forbidden", "code": 403}'));

        try {
            $transport->request('GET', '/api/v2/acquirers/branches');
            $this->fail('DiiaApiException expected');
        } catch (DiiaApiException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
            $this->assertSame('403', $exception->getErrorCode());
            $this->assertSame(
                'Diia API GET /api/v2/acquirers/branches failed: the token request failed with HTTP 403',
                $exception->getMessage()
            );
            // Guzzle's own exception quotes /api/v1/auth/acquirer/<acquirer token>.
            $this->assertNull($exception->getPrevious());
        }
    }

    public function testTokenRequestWithoutResponseDoesNotLeakToken(): void
    {
        $transport = $this->transport([], new ConnectException(
            'cURL error 7: Connection refused for https://api.diia.test/api/v1/auth/acquirer/acquirerToken',
            new Request('GET', '/api/v1/auth/acquirer/acquirerToken')
        ));

        try {
            $transport->request('GET', '/api/v2/acquirers/branches');
            $this->fail('DiiaApiException expected');
        } catch (DiiaApiException $exception) {
            $this->assertSame(0, $exception->getStatusCode());
            $this->assertStringNotContainsString('acquirerToken', $exception->getMessage());
            $this->assertStringContainsString('the token request got no response', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
        }
    }

    public function testRejectsInvalidJsonResponse(): void
    {
        $transport = $this->transport(['token' => 'eyJ...ePg'], new Response(200, [], '{"_id": '));

        $this->expectException(DiiaApiException::class);
        $transport->request('GET', '/api/v2/acquirers/branch/1');
    }
}
