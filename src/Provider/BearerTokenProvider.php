<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Provider;

use GrinchenkoUniversity\Diia\Client\AuthClient;
use GrinchenkoUniversity\Diia\Dto\Credentials;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException as InvalidCacheKeyException;

class BearerTokenProvider
{
    private const TOKEN_KEY = 'token';

    /**
     * Diia issues tokens for 2 hours; the cached copy expires 5 minutes earlier,
     * so a request made near the end of its life does not arrive with a dead token.
     */
    private const TOKEN_LIFETIME = 7200;
    private const SAFETY_MARGIN = 300;

    private AuthClient $authClient;
    private Credentials $credentials;
    private CacheInterface $cache;

    public function __construct(
        AuthClient $authClient,
        Credentials $credentials,
        CacheInterface $cache
    ) {
        $this->authClient = $authClient;
        $this->credentials = $credentials;
        $this->cache = $cache;
    }

    /**
     * @return string
     *
     * @throws GuzzleException
     * @throws InvalidCacheKeyException
     * @throws InvalidArgumentException
     */
    public function getToken(): string
    {
        $existing = $this->cache->get(self::TOKEN_KEY);

        if ($existing !== null) {
            return $existing;
        }

        $token = $this->authClient->acquireToken($this->credentials);
        $this->cache->set(self::TOKEN_KEY, $token, self::TOKEN_LIFETIME - self::SAFETY_MARGIN);

        return $token;
    }

    /**
     * Drops the cached token, so the next getToken() asks Diia for a new one.
     *
     * @throws InvalidCacheKeyException
     */
    public function invalidate(): void
    {
        $this->cache->delete(self::TOKEN_KEY);
    }
}
