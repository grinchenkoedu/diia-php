<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request;

use GrinchenkoUniversity\Diia\Dependency\DependencyResolver;
use GrinchenkoUniversity\Diia\Dto\Request\RequestInterface;
use RuntimeException;

class RequestJsonMapper
{
    private DependencyResolver $dependencyResolver;

    public function __construct(DependencyResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }

    public function mapFromRequest(RequestInterface $request): string
    {
        $requestMapper = $this->dependencyResolver->resolveByValue($request);

        if (!($requestMapper instanceof RequestMapperInterface)) {
            throw new RuntimeException('Resolved item is not RequestMapperInterface');
        }

        return json_encode($requestMapper->mapFromRequest($request));
    }
}
