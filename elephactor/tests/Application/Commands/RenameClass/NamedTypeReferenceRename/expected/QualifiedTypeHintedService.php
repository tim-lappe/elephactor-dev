<?php

namespace VirtualTestNamespace\Usage\Qualified;

final class QualifiedTypeHintedService
{
    private \VirtualTestNamespace\Types\NewDependency $dependency;

    public function transform(\VirtualTestNamespace\Types\NewDependency $dependency): \VirtualTestNamespace\Types\NewDependency
    {
        $this->dependency = $dependency;
        return $this->dependency;
    }
}
