<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request;

use GrinchenkoUniversity\Diia\Dependency\SupportedDependencyInterface;
use GrinchenkoUniversity\Diia\Dto\Request\DocumentRequest;

class DocumentRequestMapper implements RequestMapperInterface, SupportedDependencyInterface
{
    /**
     * @param DocumentRequest $dto
     * @return array
     */
    public function mapToRequest($dto): array
    {
        $request = [
            'branchId' => $dto->getBranchId(),
            'barcode' => $dto->getBarcode(),
            'requestId' => $dto->getRequestId(),
            'useDiiaId' => $dto->isUseDiiaId(),
        ];

        return $request;
    }

    public function isSupported($value): bool
    {
        return $value instanceof DocumentRequest;
    }
}
