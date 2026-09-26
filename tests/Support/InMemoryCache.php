<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Tests\Support;

use Psr\SimpleCache\CacheInterface;

/**
 * An array-backed PSR-16 cache built as a mock, so it fits psr/simple-cache 1.x (PHP 7.4)
 * and 3.x (typed signatures) alike.
 */
trait InMemoryCache
{
    /**
     * @var array<string, array{value: mixed, ttl: mixed}>
     */
    private array $cacheStore = [];

    protected function inMemoryCache(array $values = []): CacheInterface
    {
        $this->cacheStore = [];
        foreach ($values as $key => $value) {
            $this->cacheStore[$key] = ['value' => $value, 'ttl' => null];
        }

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturnCallback(function ($key, $default = null) {
            return array_key_exists($key, $this->cacheStore) ? $this->cacheStore[$key]['value'] : $default;
        });
        $cache->method('set')->willReturnCallback(function ($key, $value, $ttl = null) {
            $this->cacheStore[$key] = ['value' => $value, 'ttl' => $ttl];

            return true;
        });
        $cache->method('delete')->willReturnCallback(function ($key) {
            unset($this->cacheStore[$key]);

            return true;
        });

        return $cache;
    }

    protected function cachedTtl(string $key)
    {
        return $this->cacheStore[$key]['ttl'] ?? null;
    }

    protected function cachedValue(string $key)
    {
        return $this->cacheStore[$key]['value'] ?? null;
    }
}
