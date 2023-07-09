<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dto\Response;

class CreateResourceResponse implements ResponseInterface
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}