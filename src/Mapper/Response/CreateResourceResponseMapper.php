<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Response;

use GrinchenkoUniversity\Diia\Dto\Response\CreateResourceResponse;
use GrinchenkoUniversity\Diia\Dto\Response\ResponseInterface;

class CreateResourceResponseMapper implements ResponseMapperInterface
{
    public function mapFromResponse(array $response): ResponseInterface
    {
        return new CreateResourceResponse($response['_id']);
    }
}
