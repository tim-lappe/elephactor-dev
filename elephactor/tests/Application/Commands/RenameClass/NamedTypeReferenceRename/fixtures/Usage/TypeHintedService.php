<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Types\OldDependency;

final class TypeHintedService
{
    private OldDependency $dependency;

    public function __construct(OldDependency $dependency)
    {
        $this->dependency = $dependency;
    }

    public function replace(OldDependency $dependency): OldDependency
    {
        $this->dependency = $dependency;
        return $this->dependency;
    }
}
