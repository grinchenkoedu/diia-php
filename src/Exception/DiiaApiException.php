<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use RuntimeException;
use Throwable;

/**
 * Any failed call to the Diia API: an HTTP error status, a malformed response,
 * or a request that never got a response (status code 0).
 */
class DiiaApiException extends RuntimeException
{
    private int $statusCode;
    private ?string $errorCode;
    private ?string $responseBody;

    public function __construct(
        string $message,
        int $statusCode = 0,
        ?string $errorCode = null,
        ?string $responseBody = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);

        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->responseBody = $responseBody;
    }

    public static function fromResponse(
        string $method,
        string $path,
        ResponseInterface $response,
        ?Throwable $previous = null
    ): self {
        $body = (string) $response->getBody();
        [$diiaMessage, $errorCode] = self::parseErrorBody($body);

        return new self(
            sprintf(
                'Diia API %s %s failed with HTTP %d%s',
                $method,
                $path,
                $response->getStatusCode(),
                $diiaMessage !== null ? ': ' . $diiaMessage : ''
            ),
            $response->getStatusCode(),
            $errorCode,
            $body,
            $previous
        );
    }

    public static function fromGuzzle(string $method, string $path, GuzzleException $exception): self
    {
        if ($exception instanceof RequestException && $exception->getResponse() !== null) {
            return self::fromResponse($method, $path, $exception->getResponse(), $exception);
        }

        return new self(
            sprintf('Diia API %s %s failed: no response (%s)', $method, $path, self::describe($exception)),
            0,
            null,
            null,
            $exception
        );
    }

    /**
     * The bearer token could not be fetched for a call to $method $path.
     *
     * Deliberately not chained: the token endpoint has the acquirer token in its URL, and
     * Guzzle quotes the URL in its messages, which loggers print along with the previous exception.
     */
    public static function fromTokenRequest(string $method, string $path, GuzzleException $exception): self
    {
        $response = $exception instanceof RequestException ? $exception->getResponse() : null;

        if ($response === null) {
            return new self(sprintf(
                'Diia API %s %s failed: the token request got no response (%s)',
                $method,
                $path,
                self::describe($exception)
            ));
        }

        $body = (string) $response->getBody();
        [, $errorCode] = self::parseErrorBody($body);

        return new self(
            sprintf(
                'Diia API %s %s failed: the token request failed with HTTP %d',
                $method,
                $path,
                $response->getStatusCode()
            ),
            $response->getStatusCode(),
            $errorCode,
            $body
        );
    }

    /**
     * @return array{?string, ?string} Diia's "message" and "code", when the body has them
     */
    private static function parseErrorBody(string $body): array
    {
        $data = json_decode($body, true);

        if (!is_array($data)) {
            return [null, null];
        }

        return [
            is_string($data['message'] ?? null) ? $data['message'] : null,
            is_scalar($data['code'] ?? null) ? (string) $data['code'] : null,
        ];
    }

    /**
     * What went wrong, without Guzzle's message: it ends with the full request URL.
     */
    private static function describe(GuzzleException $exception): string
    {
        $context = $exception instanceof RequestException || $exception instanceof ConnectException
            ? $exception->getHandlerContext()
            : [];

        return is_string($context['error'] ?? null)
            ? $context['error']
            : (new ReflectionClass($exception))->getShortName();
    }

    /**
     * The HTTP status, or 0 when no response was received.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * The "code" field of Diia's error body, when it has one.
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }
}
