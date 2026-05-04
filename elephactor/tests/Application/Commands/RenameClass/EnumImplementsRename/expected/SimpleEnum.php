<?php

namespace VirtualTestNamespace\Enums;

use VirtualTestNamespace\Contracts\UpdatedBehaviorContract;

enum SimpleEnum implements UpdatedBehaviorContract
{
    case FIRST;

    public function describe(): string
    {
        return match ($this) {
            self::FIRST => 'first',
        };
    }
}
