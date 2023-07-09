<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Request\Acquirers;

use GrinchenkoUniversity\Diia\Dto\Request\RequestInterface;

class CreateBranchRequest implements RequestInterface
{
    private ?string $customFullName = null;
    private ?string $customFullAddress = null;
    private string $name;
    private ?string $email = null;
    private ?string $region = null;
    private ?string $district = null;

    /**
     * @var string City \ location name
     */
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

    /**
     * @return string|null
     */
    public function getCustomFullName(): ?string
    {
        return $this->customFullName;
    }

    /**
     * @param string|null $customFullName
     * @return CreateBranchRequest
     */
    public function setCustomFullName(?string $customFullName): CreateBranchRequest
    {
        $this->customFullName = $customFullName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomFullAddress(): ?string
    {
        return $this->customFullAddress;
    }

    /**
     * @param string|null $customFullAddress
     * @return CreateBranchRequest
     */
    public function setCustomFullAddress(?string $customFullAddress): CreateBranchRequest
    {
        $this->customFullAddress = $customFullAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return CreateBranchRequest
     */
    public function setName(string $name): CreateBranchRequest
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return CreateBranchRequest
     */
    public function setEmail(?string $email): CreateBranchRequest
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * @param string|null $region
     * @return CreateBranchRequest
     */
    public function setRegion(?string $region): CreateBranchRequest
    {
        $this->region = $region;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDistrict(): ?string
    {
        return $this->district;
    }

    /**
     * @param string|null $district
     * @return CreateBranchRequest
     */
    public function setDistrict(?string $district): CreateBranchRequest
    {
        $this->district = $district;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     * @return CreateBranchRequest
     */
    public function setLocation(string $location): CreateBranchRequest
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return CreateBranchRequest
     */
    public function setStreet(string $street): CreateBranchRequest
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * @param string $house
     * @return CreateBranchRequest
     */
    public function setHouse(string $house): CreateBranchRequest
    {
        $this->house = $house;
        return $this;
    }
}