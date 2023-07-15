<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request;

use GrinchenkoUniversity\Diia\Dependency\DependencyResolver;
use RuntimeException;

class RequestJsonMapper
{
    private DependencyResolver $dependencyResolver;
    private int $jsonEncodeFlags;

    public function __construct(
        DependencyResolver $dependencyResolver,
        int $jsonEncodeFlags = JSON_UNESCAPED_UNICODE
    ) {
        $this->dependencyResolver = $dependencyResolver;
        $this->jsonEncodeFlags = $jsonEncodeFlags;
    }

    public function mapToArray($request): array
    {
        $requestMapper = $this->dependencyResolver->resolveByValue($request);

        if ($requestMapper instanceof RequestMapperInterface) {
            return $requestMapper->mapToRequest($request);
        }

        throw new RuntimeException('Resolved item is not RequestMapperInterface');
    }

    public function mapToJson($request): string
    {
        return json_encode($this->mapToArray($request), $this->jsonEncodeFlags);
    }
}
