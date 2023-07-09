<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Request;

class ItemsListRequest implements RequestInterface
{
    private int $limit;
    private int $skip;

    public function __construct(int $limit = 10, int $skip = 0)
    {
        $this->limit = $limit;
        $this->skip = $skip;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getSkip(): int
    {
        return $this->skip;
    }
}
