<?php

namespace VirtualTestNamespace\Usage\Precedence;

use VirtualTestNamespace\Traits\LegacyTrait;
use VirtualTestNamespace\Traits\SecondaryTrait;

class PrecedenceTraitConsumer
{
    use LegacyTrait, SecondaryTrait {
        LegacyTrait::conflict insteadof SecondaryTrait;
        SecondaryTrait::conflict insteadof LegacyTrait;
    }
}
