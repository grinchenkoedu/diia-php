<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request;

use GrinchenkoUniversity\Diia\Dependency\SupportedDependencyInterface;
use GrinchenkoUniversity\Diia\Dto\Request\OfferRequest;

class OfferRequestMapper implements RequestMapperInterface, SupportedDependencyInterface
{
    /**
     * @param OfferRequest $dto
     * @return array
     */
    public function mapToRequest($dto): array
    {
        $request = [
            'offerId' => $dto->getOfferId(),
            'requestId' => $dto->getRequestId(),
            'signAlgo' => $dto->getSignAlgo(),
        ];

        if ($dto->getReturnLink() !== null) {
            $request['returnLink'] = $dto->getReturnLink();
        }

        $hashedFiles = [];

        foreach ($dto->getFiles() as $name => $hash) {
            $hashedFiles[] = [
                'fileName' => $name,
                'fileHash' => $hash,
            ];
        }

        $request['data'] = [
            'hashedFilesSigning' => [
                'hashedFiles' => $hashedFiles,
            ]
        ];

        return $request;
    }

    public function isSupported($value): bool
    {
        return $value instanceof OfferRequest;
    }
}