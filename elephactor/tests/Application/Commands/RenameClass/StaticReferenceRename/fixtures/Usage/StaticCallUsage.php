<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Utility\OldUtility;

class StaticCallUsage
{
    public function call(): string
    {
        return OldUtility::perform();
    }
}
