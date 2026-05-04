<?php

namespace VirtualTestNamespace\Usage\Precedence;

use VirtualTestNamespace\Traits\ModernTrait;
use VirtualTestNamespace\Traits\SecondaryTrait;

class PrecedenceTraitConsumer
{
    use ModernTrait, SecondaryTrait {
        ModernTrait::conflict insteadof SecondaryTrait;
        SecondaryTrait::conflict insteadof ModernTrait;
    }
}
