<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Request;

class DocumentRequest
{
    private string $branchId;
    private string $barcode;
    private string $requestId;
    private bool $useDiiaId;

    public function __construct(
        string $branchId,
        string $barcode,
        string $requestId,
        bool $useDiiaId = true
    ) {
        $this->branchId = $branchId;
        $this->barcode = $barcode;
        $this->requestId = $requestId;
        $this->useDiiaId = $useDiiaId;
    }

    public function getBranchId(): string
    {
        return $this->branchId;
    }

    public function getBarcode(): string
    {
        return $this->barcode;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function isUseDiiaId(): bool
    {
        return $this->useDiiaId;
    }
}
