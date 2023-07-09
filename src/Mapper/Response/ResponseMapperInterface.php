<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Response;

use GrinchenkoUniversity\Diia\Dto\Response\ResponseInterface;

interface ResponseMapperInterface
{
    public function mapFromResponse(array $response): ResponseInterface;
}
