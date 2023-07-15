<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Response;

use GrinchenkoUniversity\Diia\Dto\ApiResource;

class ApiResourceMapper implements ResponseMapperInterface
{
    public function mapFromResponse(array $response): ApiResource
    {
        return (new ApiResource())->setId($response['_id']);
    }
}
