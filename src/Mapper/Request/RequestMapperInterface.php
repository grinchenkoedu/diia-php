<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request;

use GrinchenkoUniversity\Diia\Dto\Request\RequestInterface;

interface RequestMapperInterface
{
    public function mapToRequest($dto): array;
}
