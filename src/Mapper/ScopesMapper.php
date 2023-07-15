<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper;

use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestMapperInterface;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseMapperInterface;

class ScopesMapper implements ResponseMapperInterface, RequestMapperInterface
{
    private array $defaultScopes;

    public function __construct(array $defaultScopes = [])
    {
        $this->defaultScopes = $defaultScopes;
    }

    /**
     * @param Scopes $dto
     *
     * @return array
     */
    public function mapToRequest($dto): array
    {
        if ($dto === null || count($dto->getAll()) < 1) {
            return $this->defaultScopes;
        }

        return $dto->getAll();
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
