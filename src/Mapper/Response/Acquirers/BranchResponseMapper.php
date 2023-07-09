<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Response\Acquirers;

use GrinchenkoUniversity\Diia\Dto\Response\Acquirers\BranchResponse;
use GrinchenkoUniversity\Diia\Dto\Response\ResponseInterface;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseMapperInterface;

class BranchResponseMapper implements ResponseMapperInterface
{
    public function mapFromResponse(array $response): ResponseInterface
    {
        return new BranchResponse(
            $response['_id'],
            $response['name'],
            $response['email'],
            $response['customFullName'],
            $response['customFullAddress'],
            $response['region'],
            $response['district'],
            $response['location'],
            $response['street'],
            $response['house']
        );
    }
}
