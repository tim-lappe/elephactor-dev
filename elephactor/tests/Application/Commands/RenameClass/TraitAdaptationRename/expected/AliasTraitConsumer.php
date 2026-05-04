<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Traits\ModernTrait;

class AliasTraitConsumer
{
    use ModernTrait {
        ModernTrait::run as runAlias;
    }
}
