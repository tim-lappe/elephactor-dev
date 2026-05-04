<?php

namespace VirtualTestNamespace\AnonymousUsage;

use VirtualTestNamespace\Anonymous\OldAnonymousBase;

class AnonymousFactory
{
    public function create(): object
    {
        return new class extends OldAnonymousBase
        {
            public function marker(): string
            {
                return 'extended';
            }
        };
    }
}
