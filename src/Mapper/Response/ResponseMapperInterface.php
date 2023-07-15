<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Response;

interface ResponseMapperInterface
{
    public function mapFromResponse(array $response);
}
