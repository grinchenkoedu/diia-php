<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto;

class ApiResource
{
    protected ?string $id = null;
    protected ?Scopes $scopes = null;

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setScopes(?Scopes $scopes): self
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @return Scopes|null
     */
    public function getScopes(): ?Scopes
    {
        return $this->scopes;
    }
}