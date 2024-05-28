<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto;

class Credentials
{
    private string $acquirerToken;
    private ?string $authAcquirerToken;

    public function __construct(string $acquirerToken, ?string $authAcquirerToken)
    {
        $this->acquirerToken = $acquirerToken;
        $this->authAcquirerToken = $authAcquirerToken;
    }

    public function getAcquirerToken(): string
    {
        return $this->acquirerToken;
    }

    public function getAuthAcquirerToken(): ?string
    {
        return $this->authAcquirerToken;
    }
}
