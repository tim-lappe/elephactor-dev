<?php

namespace VirtualTestNamespace\Usage\Advanced;

class QualifiedInstanceofChecker
{
    public function matches(object $value): bool
    {
        return $value instanceof \VirtualTestNamespace\Types\OldType;
    }
}
