<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Http;

use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Provider\BearerTokenProvider;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Sends authorized JSON requests to the Diia API.
 *
 * A 401 means the cached token was revoked or expired early: the token is dropped,
 * a new one is fetched, and the request is sent once more.
 */
final class ApiTransport
{
    private ClientInterface $httpClient;
    private BearerTokenProvider $bearerTokenProvider;
    private LoggerInterface $logger;

    public function __construct(
        ClientInterface $httpClient,
        BearerTokenProvider $bearerTokenProvider,
        ?LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->bearerTokenProvider = $bearerTokenProvider;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array|null $body JSON body, or null to send none
     * @param array $query
     *
     * @return array the decoded response body, empty when the response has none
     *
     * @throws DiiaApiException
     */
    public function request(string $method, string $path, ?array $body = null, array $query = []): array
    {
        $response = $this->send($method, $path, $body, $query);

        if ($response->getStatusCode() === 401) {
            $this->logger->warning('Diia API rejected the bearer token, requesting a new one', [
                'method' => $method,
                'path' => $path,
            ]);
            $this->bearerTokenProvider->invalidate();
            $response = $this->send($method, $path, $body, $query);
        }

        if ($response->getStatusCode() >= 400) {
            throw DiiaApiException::fromResponse($method, $path, $response);
        }

        return $this->decode($method, $path, $response);
    }

    private function send(string $method, string $path, ?array $body, array $query): ResponseInterface
    {
        try {
            $options = [
                'headers' => [
                    'accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->bearerTokenProvider->getToken(),
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false,
            ];

            if ($body !== null) {
                $options['body'] = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }

            if ($query !== []) {
                $options['query'] = $query;
            }

            return $this->httpClient->request($method, $path, $options);
        } catch (GuzzleException $exception) {
            throw DiiaApiException::fromGuzzle($method, $path, $exception);
        }
    }

    private function decode(string $method, string $path, ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $data = null;
        }

        if (!is_array($data)) {
            throw new DiiaApiException(
                sprintf('Diia API %s %s returned a body that is not a JSON object', $method, $path),
                $response->getStatusCode(),
                null,
                $body
            );
        }

        return $data;
    }
}
