<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Acquirers;

use GrinchenkoUniversity\Diia\Dependency\SupportedDependencyInterface;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestMapperInterface;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseMapperInterface;
use GrinchenkoUniversity\Diia\Mapper\ScopesMapper;
use RuntimeException;

class BranchMapper implements ResponseMapperInterface, RequestMapperInterface, SupportedDependencyInterface
{
    private ScopesMapper $scopesMapper;

    public function __construct(ScopesMapper $scopesMapper)
    {
        $this->scopesMapper = $scopesMapper;
    }

    /**
     * @param Branch $dto
     * @return array
     */
    public function mapToRequest($dto): array
    {
        if (!($dto instanceof Branch)) {
            throw new RuntimeException('Provided DTO is not supported');
        }

        $data = [
            'name' => $dto->getName(),
            'location' => $dto->getLocation(),
            'street' => $dto->getStreet(),
            'house' => $dto->getHouse(),
            'deliveryTypes' => ['api'],
            'offerRequestType' => 'dynamic',
            'scopes' => $this->scopesMapper->mapToRequest($dto->getScopes()),
        ];

        if ($dto->getCustomFullName() !== null) {
            $data['customFullName'] = $dto->getCustomFullName();
        }

        if ($dto->getCustomFullAddress() !== null) {
            $data['customFullAddress'] = $dto->getCustomFullAddress();
        }

        if ($dto->getEmail() !== null) {
            $data['email'] = $dto->getEmail();
        }

        if ($dto->getRegion() !== null) {
            $data['region'] = $dto->getRegion();
        }

        if ($dto->getDistrict() !== null) {
            $data['district'] = $dto->getDistrict();
        }

        return $data;
    }

    public function mapFromResponse(array $response): Branch
    {
        $branch = new Branch(
            $response['name'],
            $response['location'],
            $response['street'],
            $response['house']
        );

        return $branch
            ->setId($response['_id'])
            ->setCustomFullName($response['customFullName'] ?? null)
            ->setCustomFullAddress($response['customFullAddress'] ?? null)
            ->setRegion($response['region'])
            ->setDistrict($response['house'])
            ->setEmail($response['email'])
            ->setScopes($this->scopesMapper->mapFromResponse($response['scopes']))
        ;
    }

    public function isSupported($value): bool
    {
        return $value instanceof Branch;
    }
}
