<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Attributes\OldAttribute;

#[OldAttribute('simple')]
class SimpleAttributeUsage
{
    #[OldAttribute('property')]
    public function demo(): void
    {
    }
}
