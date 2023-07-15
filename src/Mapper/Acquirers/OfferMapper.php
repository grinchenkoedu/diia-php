<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Acquirers;

use GrinchenkoUniversity\Diia\Dependency\SupportedDependencyInterface;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestMapperInterface;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseMapperInterface;
use GrinchenkoUniversity\Diia\Mapper\ScopesMapper;

class OfferMapper implements RequestMapperInterface, ResponseMapperInterface, SupportedDependencyInterface
{
    private ScopesMapper $scopesMapper;

    public function __construct(ScopesMapper $scopesMapper)
    {
        $this->scopesMapper = $scopesMapper;
    }

    /**
     * @param Offer $dto
     * @return array
     */
    public function mapToRequest($dto): array
    {
        $data = [
            'name' => $dto->getName(),
            'scopes' => [
                'diiaId' => [
                    'hashedFilesSigning',
                ],
            ],
        ];

        if ($dto->getReturnLink() !== null) {
            $data['returnLink'] = $dto->getReturnLink();
        }

        return $data;
    }

    /**
     * @param array $response
     *
     * @return Offer
     */
    public function mapFromResponse(array $response)
    {
        $dto = new Offer($response['name']);

        return $dto
            ->setId($response['_id'])
            ->setReturnLink($response['returnLink'])
            ->setScopes($this->scopesMapper->mapFromResponse($response['scopes']))
        ;
    }

    public function isSupported($value): bool
    {
        return $value instanceof Offer;
    }
}
