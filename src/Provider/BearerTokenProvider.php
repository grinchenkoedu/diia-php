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
    private const TOKEN_TTL = 7200;

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
        $this->cache->set(self::TOKEN_KEY, $token, self::TOKEN_TTL);

        return $token;
    }
}