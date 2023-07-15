<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Response;

use GrinchenkoUniversity\Diia\Dto\ApiResource;

class ItemsListResponse
{
    private array $items;
    private ?int $total;

    public function __construct(array $items = [], int $total = null)
    {
        $this->items = $items;
        $this->total = $total;
    }

    public function addItem(ApiResource $item): self
    {
        $this->items[] = $item;
        reset($this->items);

        return $this;
    }

    /**
     * @return ApiResource[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function setTotal(?int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }
}