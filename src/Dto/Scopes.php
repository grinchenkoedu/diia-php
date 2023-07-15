<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto;

class Scopes
{
    private array $scopes = [];

    /**
     * @param string $name
     *
     * @param string[] $scopes
     *
     * @return self
     */
    public function addScopes(string $name, array $scopes): self
    {
        $this->scopes[$name] = $scopes;

        return $this;
    }

    public function getAll(): array
    {
        return $this->scopes;
    }
}
