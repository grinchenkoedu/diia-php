<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request;

use GrinchenkoUniversity\Diia\Dependency\SupportedDependencyInterface;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Request\RequestInterface;

class ItemsListRequestMapper implements RequestMapperInterface, SupportedDependencyInterface
{
    /**
     * @param RequestInterface|ItemsListRequest $request
     * @return array
     */
    public function mapFromRequest(RequestInterface $request): array
    {
        return [
            'skip' => $request->getSkip(),
            'limit' => $request->getLimit(),
        ];
    }

    public function isSupported($value): bool
    {
        return $value instanceof ItemsListRequest;
    }
}
