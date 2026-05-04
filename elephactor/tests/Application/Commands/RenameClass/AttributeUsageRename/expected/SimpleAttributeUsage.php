<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Attributes\NewAttribute;

#[NewAttribute('simple')]
class SimpleAttributeUsage
{
    #[NewAttribute('property')]
    public function demo(): void
    {
    }
}
