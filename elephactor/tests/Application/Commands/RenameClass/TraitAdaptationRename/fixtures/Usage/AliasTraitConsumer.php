<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Traits\LegacyTrait;

class AliasTraitConsumer
{
    use LegacyTrait {
        LegacyTrait::run as runAlias;
    }
}
