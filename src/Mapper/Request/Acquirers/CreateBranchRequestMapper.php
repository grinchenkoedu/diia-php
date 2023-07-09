<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Request\Acquirers;

use GrinchenkoUniversity\Diia\Dependency\SupportedDependencyInterface;
use GrinchenkoUniversity\Diia\Dto\Request\Acquirers\CreateBranchRequest;
use GrinchenkoUniversity\Diia\Dto\Request\RequestInterface;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestMapperInterface;

class CreateBranchRequestMapper implements RequestMapperInterface, SupportedDependencyInterface
{
    /**
     * @param RequestInterface|CreateBranchRequest $request
     * @return array
     */
    public function mapFromRequest(RequestInterface $request): array
    {
        $data = [
            'name' => $request->getName(),
            'location' => $request->getLocation(),
            'street' => $request->getStreet(),
            'house' => $request->getHouse(),
            'deliveryTypes' => ['api'],
            'offerRequestType' => 'dynamic',
            'scopes' => [
                'diiaId' => 'hashedFilesSigning'
            ],
        ];

        if ($request->getCustomFullName() !== null) {
            $data['customFullName'] = $request->getCustomFullName();
        }

        if ($request->getCustomFullAddress() !== null) {
            $data['customFullAddress'] = $request->getCustomFullAddress();
        }

        if ($request->getEmail() !== null) {
            $data['email'] = $request->getEmail();
        }

        if ($request->getRegion() !== null) {
            $data['region'] = $request->getRegion();
        }

        if ($request->getDistrict() !== null) {
            $data['district'] = $request->getDistrict();
        }

        return $data;
    }

    public function isSupported($value): bool
    {
        return $value instanceof CreateBranchRequest;
    }
}
