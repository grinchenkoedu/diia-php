<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Response;

use UnexpectedValueException;

class ResponseJsonMapper
{
    private ResponseMapperInterface $responseMapper;

    public function __construct(ResponseMapperInterface $responseMapper)
    {
        $this->responseMapper = $responseMapper;
    }

    public function mapFromResponse(string $responseBody)
    {
        $data = json_decode($responseBody, true);

        if ($data === null) {
            throw new UnexpectedValueException('Bad response body provided');
        }

        return $this->responseMapper->mapFromResponse($data);
    }
}
