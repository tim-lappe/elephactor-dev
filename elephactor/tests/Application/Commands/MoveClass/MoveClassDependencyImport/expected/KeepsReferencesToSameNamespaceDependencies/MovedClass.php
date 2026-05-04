<?php

namespace VirtualTestNamespace\Domain\Target;

class MovedClass
{
    public function __construct(private \VirtualTestNamespace\Domain\Shared\DependencyClass $dependency)
    {
    }

    public function dependency(): \VirtualTestNamespace\Domain\Shared\DependencyClass
    {
        return $this->dependency;
    }
}
