<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper;

use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestMapperInterface;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseMapperInterface;

class ScopesMapper implements ResponseMapperInterface, RequestMapperInterface
{
    private Scopes $defaultScopes;

    public function __construct(Scopes $defaultScopes = null)
    {
        $this->defaultScopes = $defaultScopes ?? new Scopes();
    }

    /**
     * @param Scopes|null $dto
     *
     * @return array
     */
    public function mapToRequest($dto): array
    {
        return ($dto ?? $this->defaultScopes)->getAll();
    }

    public function mapFromResponse(array $response): Scopes
    {
        $dto = new Scopes();

        foreach ($response as $name => $scopes) {
            $dto->addScopes($name, $scopes);
        }

        return $dto;
    }
}
