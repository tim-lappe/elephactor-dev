<?php

namespace VirtualTestNamespace\Usage\Qualified;

#[\VirtualTestNamespace\Attributes\NewAttribute('qualified')]
class QualifiedAttributeUsage
{
    #[\VirtualTestNamespace\Attributes\NewAttribute('method')]
    public function demo(): void
    {
    }
}
