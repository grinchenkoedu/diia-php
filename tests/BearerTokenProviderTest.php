<?php

use GrinchenkoUniversity\Diia\Client\AuthClient;
use GrinchenkoUniversity\Diia\Dto\Credentials;
use GrinchenkoUniversity\Diia\Provider\BearerTokenProvider;
use GrinchenkoUniversity\Diia\Tests\Support\ApiFixtures;
use GrinchenkoUniversity\Diia\Tests\Support\InMemoryCache;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class BearerTokenProviderTest extends TestCase
{
    use ApiFixtures;
    use InMemoryCache;

    public function testCachesTokenWithSafetyMargin(): void
    {
        $provider = new BearerTokenProvider(
            new AuthClient($this->httpClientReplaying(new Response(200, [], '{"token": "token-1"}'))),
            new Credentials('acquirerToken', null),
            $this->inMemoryCache()
        );

        $this->assertSame('token-1', $provider->getToken());
        $this->assertSame('token-1', $provider->getToken());
        $this->assertSame(1, $this->recordedRequestCount());
        // Diia tokens live 7200 s; the cached copy expires 5 minutes earlier.
        $this->assertSame(6900, $this->cachedTtl('token'));
    }

    public function testInvalidateForcesNewToken(): void
    {
        $provider = new BearerTokenProvider(
            new AuthClient($this->httpClientReplaying(
                new Response(200, [], '{"token": "token-1"}'),
                new Response(200, [], '{"token": "token-2"}')
            )),
            new Credentials('acquirerToken', null),
            $this->inMemoryCache()
        );

        $this->assertSame('token-1', $provider->getToken());
        $provider->invalidate();
        $this->assertNull($this->cachedValue('token'));
        $this->assertSame('token-2', $provider->getToken());
        $this->assertSame(2, $this->recordedRequestCount());
    }
}
