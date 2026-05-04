<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Types\NewDependency;

final class TypeHintedService
{
    private NewDependency $dependency;

    public function __construct(NewDependency $dependency)
    {
        $this->dependency = $dependency;
    }

    public function replace(NewDependency $dependency): NewDependency
    {
        $this->dependency = $dependency;
        return $this->dependency;
    }
}
