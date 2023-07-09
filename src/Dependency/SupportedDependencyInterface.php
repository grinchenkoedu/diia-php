<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dependency;

interface SupportedDependencyInterface
{
    public function isSupported($value): bool;
}