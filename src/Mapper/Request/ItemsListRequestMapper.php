<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request;

use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;

class ItemsListRequestMapper implements RequestMapperInterface
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
}
