<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Response;

class OfferResponse
{
    private string $deepLink;

    public function __construct(string $deepLink)
    {
        $this->deepLink = $deepLink;
    }

    public function getDeepLink(): string
    {
        return $this->deepLink;
    }
}
