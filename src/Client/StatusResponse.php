<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Client;

use GrinchenkoUniversity\Diia\Exception\DiiaApiException;

/**
 * @internal reads the "status" field shared by the offer-request and document-request status endpoints
 */
final class StatusResponse
{
    public static function status(array $data, string $path): string
    {
        if (!is_string($data['status'] ?? null)) {
            throw new DiiaApiException(
                sprintf('Diia API GET %s returned no status', $path),
                200,
                null,
                json_encode($data, JSON_UNESCAPED_UNICODE) ?: null
            );
        }

        return $data['status'];
    }
}
