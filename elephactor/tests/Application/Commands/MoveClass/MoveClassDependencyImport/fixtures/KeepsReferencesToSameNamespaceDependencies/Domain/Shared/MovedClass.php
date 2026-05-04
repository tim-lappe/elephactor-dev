<?php

namespace VirtualTestNamespace\Domain\Shared;

class MovedClass
{
    public function __construct(private DependencyClass $dependency)
    {
    }

    public function dependency(): DependencyClass
    {
        return $this->dependency;
    }
}
