<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request;

use GrinchenkoUniversity\Diia\Dto\Request\DocumentRequest;

class DocumentRequestMapper implements RequestMapperInterface
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
}
