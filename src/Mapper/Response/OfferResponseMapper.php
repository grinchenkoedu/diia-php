<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Response;

use GrinchenkoUniversity\Diia\Dto\Response\OfferResponse;

class OfferResponseMapper implements ResponseMapperInterface
{
    public function mapFromResponse(array $response): OfferResponse
    {
        return new OfferResponse($response['deeplink']);
    }
}
