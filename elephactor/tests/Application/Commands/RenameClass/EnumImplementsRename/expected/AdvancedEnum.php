<?php

namespace VirtualTestNamespace\Enums\Advanced;

use VirtualTestNamespace\Contracts\ExtraBehavior;

enum AdvancedEnum implements \VirtualTestNamespace\Contracts\UpdatedBehaviorContract, ExtraBehavior
{
    case VALUE;

    public function describe(): string
    {
        return 'advanced';
    }
}
