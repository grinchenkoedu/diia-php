<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Tests\Support;

use GrinchenkoUniversity\Diia\Http\ApiTransport;
use GrinchenkoUniversity\Diia\Provider\BearerTokenProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

/**
 * Replays recorded Diia API exchanges from tests/fixtures/requests/*.json.
 *
 * The fixtures freeze the requests 1.0 sends. A client whose request differs from its fixture
 * has changed Diia's contract: that is a regression, not a fixture to update.
 */
trait ApiFixtures
{
    /**
     * Headers Guzzle adds on its own; they are not part of what the library sends.
     */
    private static array $transportHeaders = ['host', 'user-agent', 'content-length'];

    private array $httpHistory = [];

    protected function loadFixture(string $name): array
    {
        $path = __DIR__ . '/../fixtures/requests/' . $name . '.json';
        $data = json_decode((string) file_get_contents($path), true);

        if (!is_array($data) || !isset($data['request'], $data['response'])) {
            throw new RuntimeException(sprintf('Bad fixture: %s', $name));
        }

        return $data;
    }

    protected function fixtureResponse(string $name): ResponseInterface
    {
        $response = $this->loadFixture($name)['response'];
        $body = $response['body'] === null ? '' : json_encode($response['body'], JSON_UNESCAPED_UNICODE);

        return new Response($response['status'], ['Content-Type' => 'application/json'], $body);
    }

    /**
     * @param string|ResponseInterface|Throwable ...$responses fixture names, ready responses or
     *                                                        transport errors, in call order
     */
    protected function httpClientReplaying(...$responses): Client
    {
        return new Client([
            'handler' => $this->handlerReplaying(...$responses),
            'base_uri' => 'https://api.diia.test',
        ]);
    }

    /**
     * @param string|ResponseInterface|Throwable ...$responses
     */
    protected function handlerReplaying(...$responses): HandlerStack
    {
        $queue = [];

        foreach ($responses as $response) {
            $queue[] = is_string($response) ? $this->fixtureResponse($response) : $response;
        }

        $this->httpHistory = [];
        $stack = HandlerStack::create(new MockHandler($queue));
        $stack->push(Middleware::history($this->httpHistory));

        return $stack;
    }

    /**
     * A transport whose token is the one in the fixtures' Authorization header.
     *
     * @param string|ResponseInterface|Throwable ...$responses
     */
    protected function transportReplaying(...$responses): ApiTransport
    {
        return new ApiTransport(
            $this->httpClientReplaying(...$responses),
            $this->createConfiguredMock(BearerTokenProvider::class, ['getToken' => 'eyJ...ePg'])
        );
    }

    protected function recordedRequest(int $index): RequestInterface
    {
        $this->assertArrayHasKey($index, $this->httpHistory, sprintf('No request #%d was sent', $index));

        return $this->httpHistory[$index]['request'];
    }

    protected function recordedRequestCount(): int
    {
        return count($this->httpHistory);
    }

    protected function assertRequestMatchesFixture(string $name, int $index = 0): void
    {
        $expected = $this->loadFixture($name)['request'];
        $request = $this->recordedRequest($index);

        parse_str($request->getUri()->getQuery(), $query);

        $headers = [];
        foreach ($request->getHeaders() as $header => $values) {
            $header = strtolower($header);
            if (!in_array($header, self::$transportHeaders, true)) {
                $headers[$header] = implode(', ', $values);
            }
        }
        ksort($headers);
        $expectedHeaders = $expected['headers'];
        ksort($expectedHeaders);

        $rawBody = (string) $request->getBody();
        $body = $rawBody === '' ? null : json_decode($rawBody, true);

        $this->assertSame($expected['method'], $request->getMethod(), "$name: method");
        $this->assertSame($expected['path'], $request->getUri()->getPath(), "$name: path");
        $this->assertSame($expected['query'], $query, "$name: query");
        $this->assertSame($expectedHeaders, $headers, "$name: headers");
        $this->assertSame($expected['body'], $body, "$name: body");
    }
}
