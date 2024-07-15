<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Acquirers;

use GrinchenkoUniversity\Diia\Dto\ApiResource;

class Branch extends ApiResource
{
    private string $name;
    private ?string $email = null;
    private ?string $customFullName = null;
    private ?string $customFullAddress = null;
    private ?string $region = null;
    private ?string $district = null;
    private string $location;
    private string $street;
    private string $house;

    public function __construct(
        string $name,
        string $location,
        string $street,
        string $house
    ) {
        $this->name = $name;
        $this->location = $location;
        $this->street = $street;
        $this->house = $house;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getCustomFullName(): ?string
    {
        return $this->customFullName;
    }

    public function setCustomFullName(?string $customFullName): self
    {
        $this->customFullName = $customFullName;
        return $this;
    }

    public function getCustomFullAddress(): ?string
    {
        return $this->customFullAddress;
    }

    public function setCustomFullAddress(?string $customFullAddress): self
    {
        $this->customFullAddress = $customFullAddress;
        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;
        return $this;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(?string $district): self
    {
        $this->district = $district;
        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;
        return $this;
    }

    public function getHouse(): string
    {
        return $this->house;
    }

    public function setHouse(string $house): self
    {
        $this->house = $house;
        return $this;
    }
}
