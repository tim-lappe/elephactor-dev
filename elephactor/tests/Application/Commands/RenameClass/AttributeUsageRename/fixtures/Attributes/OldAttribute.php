<?php

namespace VirtualTestNamespace\Attributes;

#[\Attribute(\Attribute::TARGET_ALL)]
class OldAttribute
{
    public function __construct(public string $value = '')
    {
    }
}
