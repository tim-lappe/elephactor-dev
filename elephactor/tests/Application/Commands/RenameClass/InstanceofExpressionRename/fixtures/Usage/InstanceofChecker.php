<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Types\OldType;

class InstanceofChecker
{
    public function matches(object $value): bool
    {
        return $value instanceof OldType;
    }
}
