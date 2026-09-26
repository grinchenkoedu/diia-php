<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Exception;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
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
        $data = json_decode($body, true);
        $diiaMessage = is_array($data) && is_string($data['message'] ?? null) ? $data['message'] : null;
        $errorCode = is_array($data) && is_scalar($data['code'] ?? null) ? (string) $data['code'] : null;

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
            sprintf('Diia API %s %s failed: %s', $method, $path, $exception->getMessage()),
            0,
            null,
            null,
            $exception
        );
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
