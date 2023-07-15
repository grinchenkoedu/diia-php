<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Acquirers;

use GrinchenkoUniversity\Diia\Dto\ApiResource;

class Offer extends ApiResource
{
    private string $name;
    private ?string $returnLink = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setReturnLink(?string $returnLink): self
    {
        $this->returnLink = $returnLink;

        return $this;
    }

    public function getReturnLink(): ?string
    {
        return $this->returnLink;
    }
}
