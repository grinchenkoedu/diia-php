<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request;

use GrinchenkoUniversity\Diia\Dependency\SupportedDependencyInterface;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;

class ItemsListRequestMapper implements RequestMapperInterface, SupportedDependencyInterface
{
    /**
     * @param ItemsListRequest $dto
     * @return array
     */
    public function mapToRequest($dto): array
    {
        return [
            'skip' => $dto->getSkip(),
            'limit' => $dto->getLimit(),
        ];
    }

    public function isSupported($value): bool
    {
        return $value instanceof ItemsListRequest;
    }
}
