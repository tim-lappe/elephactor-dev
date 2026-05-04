<?php

namespace VirtualTestNamespace\Usage\Qualified;

#[\VirtualTestNamespace\Attributes\OldAttribute('qualified')]
class QualifiedAttributeUsage
{
    #[\VirtualTestNamespace\Attributes\OldAttribute('method')]
    public function demo(): void
    {
    }
}
