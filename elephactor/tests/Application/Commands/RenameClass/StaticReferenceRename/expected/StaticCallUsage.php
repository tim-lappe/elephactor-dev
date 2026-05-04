<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Utility\NewUtility;

class StaticCallUsage
{
    public function call(): string
    {
        return NewUtility::perform();
    }
}
