<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Dependency;

use InvalidArgumentException;

final class DependencyResolver
{
    /**
     * @var SupportedDependencyInterface[]
     */
    private array $dependencies = [];

    public function addDependency(SupportedDependencyInterface $dependency): self
    {
        if (in_array($dependency, $this->dependencies, true)) {
            throw new InvalidArgumentException('Provided dependency already set: ' . get_class($dependency));
        }

        $this->dependencies[] = $dependency;

        return $this;
    }

    public function resolveByValue($value): SupportedDependencyInterface
    {
        foreach ($this->dependencies as $dependency) {
            if ($dependency->isSupported($value)) {
                return $dependency;
            }
        }

        throw new UnresolvedDependencyException(
            sprintf(
                'No supported dependency for provided value: %s',
                is_object($value) ? get_class($value) : gettype($value)
            )
        );
    }
}