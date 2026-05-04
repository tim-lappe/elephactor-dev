<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Types\NewType;

class InstanceofChecker
{
    public function matches(object $value): bool
    {
        return $value instanceof NewType;
    }
}
