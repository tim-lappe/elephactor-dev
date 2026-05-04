<?php

namespace VirtualTestNamespace\Enums;

use VirtualTestNamespace\Contracts\BehaviorContract;

enum SimpleEnum implements BehaviorContract
{
    case FIRST;

    public function describe(): string
    {
        return match ($this) {
            self::FIRST => 'first',
        };
    }
}
