<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Provider;

class HttpHeadersProvider
{
    private BearerTokenProvider $bearerTokenProvider;

    public function __construct(BearerTokenProvider $bearerTokenProvider)
    {
        $this->bearerTokenProvider = $bearerTokenProvider;
    }

    /**
     * @return string[]
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getDefaultHeaders(): array
    {
        return [
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->bearerTokenProvider->getToken(),
            'Content-Type' => 'application/json',
        ];
    }
}