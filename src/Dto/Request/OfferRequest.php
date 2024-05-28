<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Request;

class OfferRequest
{
    const SIGN_ALGO_DSTU = 'DSTU';
    const SIGN_ALGO_ECDSA = 'ECDSA';

    private string $offerId;
    private ?string $returnLink = null;
    private string $requestId;
    private ?string $signAlgo = null;
    private ?bool $useDiia = null;
    private array $files = [];

    public function __construct(
        string $offerId,
        string $requestId
    ) {
        $this->offerId = $offerId;
        $this->requestId = $requestId;
    }

    /**
     * @see https://www.php.net/manual/en/function.hash-file.php
     *
     * @param string $name name of the file like "filename_1.pdf"
     * @param string $hash base64 of the hash generated using sha256 algo
     *
     * @return self
     */
    public function addFile(string $name, string $hash): self
    {
        $this->files[$name] = $hash;

        return $this;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getOfferId(): string
    {
        return $this->offerId;
    }

    public function setOfferId(string $offerId): self
    {
        $this->offerId = $offerId;

        return $this;
    }

    public function getReturnLink(): ?string
    {
        return $this->returnLink;
    }

    public function setReturnLink(?string $returnLink): self
    {
        $this->returnLink = $returnLink;

        return $this;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): self
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getSignAlgo(): ?string
    {
        return $this->signAlgo;
    }

    public function setSignAlgo(string $signAlgo): self
    {
        $this->signAlgo = $signAlgo;

        return $this;
    }

    public function getUseDiia(): ?bool
    {
        return $this->useDiia;
    }

    public function setUseDiia(?bool $useDiia): self
    {
        $this->useDiia = $useDiia;

        return $this;
    }
}
