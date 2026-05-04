<?php

namespace VirtualTestNamespace\Usage\Qualified;

final class QualifiedTypeHintedService
{
    private \VirtualTestNamespace\Types\OldDependency $dependency;

    public function transform(\VirtualTestNamespace\Types\OldDependency $dependency): \VirtualTestNamespace\Types\OldDependency
    {
        $this->dependency = $dependency;
        return $this->dependency;
    }
}
