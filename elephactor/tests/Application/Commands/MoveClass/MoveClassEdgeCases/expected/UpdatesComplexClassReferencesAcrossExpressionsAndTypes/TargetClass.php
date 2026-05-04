<?php

namespace VirtualTestNamespace\Refactored\Core;

#[\Attribute]
final class TargetClass
{
    public const CONSTANT = 'initial';

    public static int $counter = 0;

    public static function create(): self
    {
        return new self();
    }
}
