<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Response\Acquirers;

use GrinchenkoUniversity\Diia\Dto\Response\ResponseInterface;

class BranchResponse implements ResponseInterface
{
    private string $id;
    private string $name;
    private ?string $email;
    private ?string $customFullName;
    private ?string $customFullAddress;
    private ?string $region;
    private ?string $district;
    private string $location;
    private string $street;
    private string $house;

    public function __construct(
        string $id,
        string $name,
        ?string $email,
        ?string $customFullName,
        ?string $customFullAddress,
        ?string $region,
        ?string $district,
        string $location,
        string $street,
        string $house
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->customFullName = $customFullName;
        $this->customFullAddress = $customFullAddress;
        $this->region = $region;
        $this->district = $district;
        $this->location = $location;
        $this->street = $street;
        $this->house = $house;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getCustomFullName(): ?string
    {
        return $this->customFullName;
    }

    public function getCustomFullAddress(): ?string
    {
        return $this->customFullAddress;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getHouse(): string
    {
        return $this->house;
    }
}